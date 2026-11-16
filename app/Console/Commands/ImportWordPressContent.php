<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ArticleStatus;
use App\Enums\ProgramStatus;
use App\Enums\TopicStatus;
use App\Models\Grade;
use App\Models\Program;
use App\Models\TheoryArticle;
use App\Models\Topic;
use App\Models\Unit;
use App\Support\WordPressContent;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Imports kimyaogren.com WordPress posts as Theory Articles under the MEB
 * curriculum (Sprint 2.2, ADR-004). Safe to re-run: records are matched by
 * WordPress post id, and a new immutable version is written only when the
 * content changed.
 */
class ImportWordPressContent extends Command
{
    protected $signature = 'kimyaogren:import-wordpress
        {--base=https://kimyaogren.com : WordPress site URL}
        {--skip-images : Do not download images into public/wp-content/uploads}
        {--dry-run : Fetch and validate the mapping without writing anything}';

    protected $description = 'Import kimyaogren.com WordPress posts into the MEB curriculum as theory articles';

    /** @var list<string> */
    private array $failedImages = [];

    /** @var array<string, int> */
    private array $stats = ['articles_created' => 0, 'articles_updated' => 0, 'versions_created' => 0, 'images_downloaded' => 0, 'images_existing' => 0, 'images_failed' => 0];

    public function handle(): int
    {
        $mapping = require database_path('data/meb-kimya-mufredat.php');
        $base = rtrim((string) $this->option('base'), '/');

        $posts = $this->fetchAll($base, 'posts', 'id,slug,title,content,excerpt,date,modified,featured_media');
        $this->info("WordPress: {$posts->count()} yazı alındı.");

        $placement = $this->placementIndex($mapping['grades']);
        $unmapped = $posts->reject(fn (array $post) => isset($placement[$post['slug']]));

        if ($unmapped->isNotEmpty()) {
            $this->warn('Eşlemesi olmayan yazılar atlanacak ('.$unmapped->count().'): '.$unmapped->pluck('slug')->join(', '));
        }

        $posts = $posts->diffKeys($unmapped);

        if ($this->option('dry-run')) {
            $this->info("Kuru çalıştırma: {$posts->count()} yazı eşlendi, hiçbir şey yazılmadı.");

            return self::SUCCESS;
        }

        $featured = $this->featuredImages($base, $posts->pluck('featured_media')->filter()->unique()->values());

        $images = DB::transaction(function () use ($mapping, $posts, $placement, $featured): array {
            $topics = $this->syncCurriculum($mapping);

            return $this->syncArticles($posts, $placement, $topics, $featured);
        });

        if (! $this->option('skip-images')) {
            $this->downloadImages($base, $images);
        }

        $this->table(['Sonuç', 'Adet'], collect($this->stats)->map(fn ($count, $key) => [$key, $count])->values()->all());

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchAll(string $base, string $endpoint, string $fields, array $query = []): Collection
    {
        $items = collect();
        $page = 1;

        do {
            $response = Http::acceptJson()->retry(3, 1000)->timeout(60)
                ->get("{$base}/wp-json/wp/v2/{$endpoint}", $query + ['per_page' => 100, 'page' => $page, '_fields' => $fields]);

            if (! $response->successful()) {
                throw new RuntimeException("WordPress API hatası ({$endpoint}, sayfa {$page}): HTTP {$response->status()}");
            }

            $items = $items->concat($response->json());
            $totalPages = (int) ($response->header('X-WP-TotalPages') ?: 1);
        } while (++$page <= $totalPages);

        return $items->keyBy('id');
    }

    /**
     * @param  Collection<int, int>  $ids
     * @return array<int, string> media id => root-relative upload path
     */
    private function featuredImages(string $base, Collection $ids): array
    {
        $paths = [];

        // Keyed by media id — must not go through flatMap/collapse, which renumbers keys.
        foreach ($ids->chunk(100) as $chunk) {
            foreach ($this->fetchAll($base, 'media', 'id,source_url', ['include' => $chunk->join(',')]) as $id => $media) {
                if ($path = WordPressContent::uploadPath($media['source_url'] ?? null)) {
                    $paths[(int) $id] = $path;
                }
            }
        }

        return $paths;
    }

    /**
     * @param  array<int, array<int, array<string, mixed>>>  $grades
     * @return array<string, array{grade: int, unit: int, topic: int}>
     */
    private function placementIndex(array $grades): array
    {
        $index = [];

        foreach ($grades as $grade => $units) {
            foreach ($units as $unitOrder => $unit) {
                foreach ($unit['topics'] as $topicOrder => $topic) {
                    foreach ($topic['posts'] as $slug) {
                        $index[$slug] = ['grade' => $grade, 'unit' => $unitOrder, 'topic' => $topicOrder];
                    }
                }
            }
        }

        return $index;
    }

    /**
     * Create (or reuse) the program → grade → unit → topic tree.
     *
     * @param  array<string, mixed>  $mapping
     * @return array<string, Topic> "grade.unit.topic" => Topic
     */
    private function syncCurriculum(array $mapping): array
    {
        $grades = $mapping['grades'];

        $program = Program::firstOrCreate(['code' => $mapping['program']['code']], [
            'name' => $mapping['program']['name'],
            'slug' => $mapping['program']['slug'],
            'short_description' => $mapping['program']['short_description'],
            'primary_language' => 'tr',
            'supported_languages' => ['tr'],
            'min_grade' => min(array_keys($grades)),
            'max_grade' => max(array_keys($grades)),
            'status' => ProgramStatus::Published,
            'is_active' => true,
            'display_order' => 0,
            'published_at' => now(),
        ]);

        $topics = [];

        foreach ($grades as $number => $units) {
            $grade = Grade::firstOrCreate(['program_id' => $program->id, 'number' => $number], [
                'name' => "{$number}. Sınıf",
                'order' => $number,
                'is_active' => true,
            ]);

            foreach ($units as $unitOrder => $unitData) {
                $unit = Unit::firstOrCreate(['grade_id' => $grade->id, 'code' => $unitData['code']], [
                    'name' => $unitData['name'],
                    'order' => $unitOrder,
                    'is_active' => true,
                ]);

                foreach ($unitData['topics'] as $topicOrder => $topicData) {
                    $topic = Topic::firstOrCreate(['unit_id' => $unit->id, 'slug' => Str::slug($topicData['name'])], [
                        'name' => $topicData['name'],
                        'order' => $topicOrder,
                        'difficulty' => $topicData['difficulty'],
                        // Topics without content stay hidden until an editor fills them.
                        'status' => $topicData['posts'] === [] ? TopicStatus::Draft : TopicStatus::Published,
                        'is_active' => true,
                    ]);

                    if ($topicData['posts'] !== [] && $topic->status === TopicStatus::Draft && ! $topic->wasRecentlyCreated) {
                        $topic->update(['status' => TopicStatus::Published]);
                    }

                    $topics["{$number}.{$unitOrder}.{$topicOrder}"] = $topic;
                }
            }
        }

        return $topics;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $posts
     * @param  array<string, array{grade: int, unit: int, topic: int}>  $placement
     * @param  array<string, Topic>  $topics
     * @param  array<int, string>  $featured
     * @return list<string> image paths to download
     */
    private function syncArticles(Collection $posts, array $placement, array $topics, array $featured): array
    {
        $images = [];

        $posts->groupBy(fn (array $post) => implode('.', $placement[$post['slug']]))
            ->each(function (Collection $topicPosts, string $key) use ($topics, $featured, &$images) {
                $topic = $topics[$key];

                // The author's publication order is the teaching order.
                $topicPosts->sortBy('date')->values()->each(function (array $post, int $index) use ($topic, $featured, &$images) {
                    $content = WordPressContent::sanitize($post['content']['rendered']);
                    $image = $featured[$post['featured_media']] ?? null;
                    $images = [...$images, ...WordPressContent::imagePaths($content), ...array_filter([$image])];

                    $this->syncArticle($topic, $post, $index + 1, $content, $image);
                });
            });

        return array_values(array_unique($images));
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function syncArticle(Topic $topic, array $post, int $order, string $content, ?string $image): void
    {
        $title = html_entity_decode($post['title']['rendered'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $excerpt = WordPressContent::excerpt($post['excerpt']['rendered'] ?? '');
        $words = WordPressContent::wordCount($content);

        $article = TheoryArticle::withTrashed()->firstOrNew(['legacy_wp_id' => $post['id']]);

        $article->fill([
            'topic_id' => $topic->id,
            'title' => Str::limit($title, 200, ''),
            'slug' => $article->exists && $article->topic_id === $topic->id ? $article->slug : $this->uniqueSlug($topic, $title, $post['slug']),
            'excerpt' => $excerpt ?: null,
            'order' => $order,
            'difficulty' => $topic->difficulty,
            'read_time_minutes' => max(1, (int) ceil($words / 200)),
            'featured_image_path' => $image,
            'legacy_slug' => $post['slug'],
            'status' => $article->exists ? $article->status : ArticleStatus::Published,
            'is_active' => $article->exists ? $article->is_active : true,
            'published_at' => Carbon::parse($post['date']),
            'meta_description' => $excerpt ? Str::limit($excerpt, 157) : null,
        ]);

        $this->stats[$article->exists ? 'articles_updated' : 'articles_created']++;
        $article->save();

        $current = $article->currentVersion()->first();

        if ($current?->content === $content && $current->title === $article->title) {
            return;
        }

        $article->versions()->update(['is_published_version' => false]);
        $article->versions()->create([
            'version_number' => (int) $article->versions()->max('version_number') + 1,
            'title' => $article->title,
            'content' => $content,
            'change_summary' => $current ? 'kimyaogren.com WordPress senkronu' : 'kimyaogren.com WordPress içe aktarımı',
            'word_count' => $words,
            'is_published_version' => true,
        ]);
        $this->stats['versions_created']++;
    }

    private function uniqueSlug(Topic $topic, string $title, string $fallback): string
    {
        $slug = Str::slug($title) ?: $fallback;
        $candidate = $slug;

        for ($i = 2; TheoryArticle::withTrashed()->where('topic_id', $topic->id)->where('slug', $candidate)->exists(); $i++) {
            $candidate = "{$slug}-{$i}";
        }

        return $candidate;
    }

    /**
     * @param  list<string>  $paths
     */
    private function downloadImages(string $base, array $paths): void
    {
        $missing = array_values(array_filter($paths, fn (string $path) => ! File::exists(public_path($path))));
        $this->stats['images_existing'] = count($paths) - count($missing);

        if ($missing === []) {
            return;
        }

        $bar = $this->output->createProgressBar(count($missing));

        foreach (array_chunk($missing, 8) as $chunk) {
            $responses = Http::pool(fn (Pool $pool) => array_map(
                fn (string $path) => $pool->as($path)->timeout(60)->get($base.implode('/', array_map('rawurlencode', explode('/', $path)))),
                $chunk,
            ));

            foreach ($chunk as $path) {
                $response = $responses[$path];

                if ($response instanceof Response && $response->successful()) {
                    File::ensureDirectoryExists(dirname(public_path($path)));
                    File::put(public_path($path), $response->body());
                    $this->stats['images_downloaded']++;
                } else {
                    $this->stats['images_failed']++;
                    $this->failedImages[] = $path;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();

        if ($this->failedImages !== []) {
            // A cover that does not exist would render as a broken image.
            TheoryArticle::whereIn('featured_image_path', $this->failedImages)->update(['featured_image_path' => null]);

            $report = storage_path('app/wordpress-import/eksik-gorseller.txt');
            File::ensureDirectoryExists(dirname($report));
            File::put($report, implode(PHP_EOL, $this->failedImages).PHP_EOL);
            $this->warn(count($this->failedImages)." görsel indirilemedi (kaynak sitede de yok olabilir). Liste: {$report}");
        }
    }
}
