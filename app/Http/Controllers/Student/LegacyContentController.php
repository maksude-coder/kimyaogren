<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Enums\TopicStatus;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\TheoryArticle;
use App\Models\Topic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Keeps kimyaogren.com's WordPress URLs alive (ADR-004). Registered as the
 * router fallback, so it only runs when no application route matched.
 */
class LegacyContentController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $path = trim($request->path(), '/');

        if (preg_match('#^category/([a-z0-9-]+)$#', $path, $m) === 1) {
            return $this->redirectCategory($m[1]);
        }

        if (preg_match('#^tag/[^/]+$#', $path) === 1) {
            return redirect()->route('curriculum.index', status: 301);
        }

        abort_unless(preg_match('#^[a-z0-9-]+$#', $path) === 1, 404);

        return $this->article($request, $path);
    }

    private function article(Request $request, string $slug): View|RedirectResponse
    {
        $article = TheoryArticle::published()
            ->where('legacy_slug', $slug)
            ->with(['currentVersion', 'topic.unit.grade.program'])
            ->first();

        abort_if($article === null || $article->currentVersion === null || ! $this->isVisible($article->topic), 404);

        // WordPress canonicalises with a trailing slash; so do we.
        if (! str_ends_with((string) parse_url($request->getRequestUri(), PHP_URL_PATH), '/')) {
            return redirect()->to($article->publicUrl(), 301);
        }

        $siblings = $article->topic->theoryArticles()->published()->ordered()->orderBy('id')
            ->get(['id', 'title', 'legacy_slug', 'topic_id']);
        $position = $siblings->search(fn (TheoryArticle $sibling) => $sibling->is($article));

        return view('student.article', [
            'article' => $article,
            'topic' => $article->topic,
            'unit' => $article->topic->unit,
            'grade' => $article->topic->unit->grade,
            'program' => $article->topic->unit->grade->program,
            'previous' => $position > 0 ? $siblings[$position - 1] : null,
            'next' => $siblings[$position + 1] ?? null,
        ]);
    }

    /**
     * The whole ownership chain must be student-visible (Sprint 2.1 plan § 3).
     */
    private function isVisible(Topic $topic): bool
    {
        $unit = $topic->unit;
        $grade = $unit?->grade;
        $program = $grade?->program;

        return $topic->is_active && $topic->status === TopicStatus::Published
            && $unit?->is_active && $grade?->is_active
            && $program !== null && Program::published()->whereKey($program->id)->exists();
    }

    private function redirectCategory(string $slug): RedirectResponse
    {
        $mapping = require database_path('data/meb-kimya-mufredat.php');
        $grade = $mapping['categories'][$slug] ?? null;
        $program = Program::published()->where('slug', $mapping['program']['slug'])->first();

        if ($grade === null || $program === null || ! $program->grades()->active()->where('number', $grade)->exists()) {
            return redirect()->route('curriculum.index', status: 301);
        }

        return redirect()->route('curriculum.grade', ['program' => $program->slug, 'grade' => $grade], 301);
    }
}
