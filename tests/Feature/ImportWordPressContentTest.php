<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\TopicStatus;
use App\Models\Program;
use App\Models\TheoryArticle;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportWordPressContentTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, array<string, mixed>> */
    private array $posts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->posts = [
            $this->wpPost(2727, 'notrallesme-tepkimeleri', 'Nötralleşme Tepkimeleri', '2022-05-09T10:00:00', 11),
            $this->wpPost(2600, 'yanmaya-karsi-asallik', 'Yanmaya Karşı Asallık', '2022-03-06T10:00:00', 12),
            $this->wpPost(9999, 'haritada-olmayan-yazi', 'Eşlenmemiş', '2022-01-01T10:00:00', 0),
        ];

        $this->fakeWordPress();
    }

    /**
     * @return array<string, mixed>
     */
    private function wpPost(int $id, string $slug, string $title, string $date, int $media, string $body = 'Asit ile baz'): array
    {
        return [
            'id' => $id,
            'slug' => $slug,
            'title' => ['rendered' => $title],
            'content' => ['rendered' => "<p>{$body}</p><p><img class=\"x\" src=\"http://kimyaogren.com/wp-content/uploads/test/{$slug}.jpg\" /></p><script>x</script>"],
            'excerpt' => ['rendered' => "<p>{$title} özeti [&hellip;]</p>"],
            'date' => $date,
            'modified' => $date,
            'featured_media' => $media,
        ];
    }

    private function fakeWordPress(): void
    {
        Http::fake([
            'kimyaogren.com/wp-json/wp/v2/posts*' => fn () => Http::response($this->posts, 200, ['X-WP-TotalPages' => '1']),
            'kimyaogren.com/wp-json/wp/v2/media*' => Http::response([
                ['id' => 11, 'source_url' => 'https://kimyaogren.com/wp-content/uploads/test/kapak-11.jpg'],
                ['id' => 12, 'source_url' => 'https://kimyaogren.com/wp-content/uploads/test/kapak-yok.jpg'],
            ], 200, ['X-WP-TotalPages' => '1']),
            'kimyaogren.com/wp-content/uploads/test/kapak-yok.jpg' => Http::response('', 404),
            'kimyaogren.com/wp-content/uploads/*' => Http::response('JPEG', 200),
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('wp-content/uploads/test'));

        parent::tearDown();
    }

    public function test_posts_are_imported_under_their_mapped_topic(): void
    {
        $this->artisan('kimyaogren:import-wordpress')->assertSuccessful();

        $program = Program::where('code', 'KIM_MEB_TR')->firstOrFail();
        $this->assertSame('lise-kimya', $program->slug);

        $article = TheoryArticle::where('legacy_slug', 'notrallesme-tepkimeleri')->with('topic.unit.grade')->firstOrFail();
        $this->assertSame('Asitlerin ve Bazların Tepkimeleri', $article->topic->name);
        $this->assertSame('10.3', $article->topic->unit->code);
        $this->assertSame(10, $article->topic->unit->grade->number);
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertSame(2727, $article->legacy_wp_id);
        $this->assertSame('Nötralleşme Tepkimeleri özeti', $article->excerpt);

        // Author's publication order: yanmaya-karsi-asallik (March) comes first.
        $this->assertSame(2, $article->order);
        $this->assertSame(1, TheoryArticle::where('legacy_slug', 'yanmaya-karsi-asallik')->value('order'));

        $this->assertDatabaseMissing('theory_articles', ['legacy_slug' => 'haritada-olmayan-yazi']);
    }

    public function test_content_is_sanitized_and_images_are_served_from_the_same_path(): void
    {
        $this->artisan('kimyaogren:import-wordpress')->assertSuccessful();

        $content = TheoryArticle::where('legacy_slug', 'notrallesme-tepkimeleri')->firstOrFail()->currentVersion->content;

        $this->assertStringContainsString('src="/wp-content/uploads/test/notrallesme-tepkimeleri.jpg"', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertFileExists(public_path('wp-content/uploads/test/notrallesme-tepkimeleri.jpg'));
        $this->assertFileExists(public_path('wp-content/uploads/test/kapak-11.jpg'));
    }

    public function test_missing_cover_images_are_cleared(): void
    {
        $this->artisan('kimyaogren:import-wordpress')->assertSuccessful();

        $this->assertSame('/wp-content/uploads/test/kapak-11.jpg', TheoryArticle::where('legacy_wp_id', 2727)->value('featured_image_path'));
        $this->assertNull(TheoryArticle::where('legacy_wp_id', 2600)->value('featured_image_path'));
    }

    public function test_topics_without_posts_are_drafts(): void
    {
        $this->artisan('kimyaogren:import-wordpress', ['--skip-images' => true])->assertSuccessful();

        $this->assertSame(TopicStatus::Draft, Topic::where('name', 'Tuzlar')->firstOrFail()->status);
        $this->assertSame(TopicStatus::Published, Topic::where('name', 'Asitlerin ve Bazların Tepkimeleri')->firstOrFail()->status);
    }

    public function test_reimport_is_idempotent_and_versions_changed_content(): void
    {
        $this->artisan('kimyaogren:import-wordpress', ['--skip-images' => true])->assertSuccessful();
        $this->artisan('kimyaogren:import-wordpress', ['--skip-images' => true])->assertSuccessful();

        $article = TheoryArticle::where('legacy_wp_id', 2727)->firstOrFail();
        $this->assertSame(2, TheoryArticle::count());
        $this->assertSame(1, $article->versions()->count());

        $this->posts[0] = $this->wpPost(2727, 'notrallesme-tepkimeleri', 'Nötralleşme Tepkimeleri', '2022-05-09T10:00:00', 11, 'Güncellendi');
        $this->artisan('kimyaogren:import-wordpress', ['--skip-images' => true])->assertSuccessful();

        $this->assertSame(2, $article->versions()->count());
        $this->assertSame(1, $article->versions()->where('is_published_version', true)->count());
        $this->assertStringContainsString('Güncellendi', $article->currentVersion()->first()->content);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->artisan('kimyaogren:import-wordpress', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(0, TheoryArticle::count());
        $this->assertSame(0, Program::count());
    }
}
