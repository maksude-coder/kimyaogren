<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Program;
use App\Models\Topic;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Read-only student curriculum browser: grade → unit → topic.
 *
 * Only published/active curriculum is reachable; anything else is a 404 so
 * draft content is never revealed (Sprint 2.1 plan § 3).
 */
class CurriculumController extends Controller
{
    /**
     * Send the student to their grade, or the first grade, of the first published program.
     */
    public function index(Request $request): RedirectResponse|View
    {
        $program = Program::published()->ordered()->first();
        $grades = $program?->grades()->active()->ordered()->get();

        // A signed-in student opens on their own grade (Sprint 2.3 plan § 1.10).
        $grade = $grades?->firstWhere('id', $request->user()?->grade_id) ?? $grades?->first();

        if ($grade === null) {
            return view('student.curriculum.empty');
        }

        return redirect()->route('curriculum.grade', [
            'program' => $program->slug,
            'grade' => $grade->number,
        ]);
    }

    /**
     * Grade tabs, the grade's units, and the selected unit's topics.
     */
    public function grade(Request $request, string $program, int $grade): View
    {
        $program = $this->findProgram($program);
        $grades = $program->grades()->active()->ordered()->get();
        $grade = $grades->firstWhere('number', $grade) ?? abort(404);

        $units = $grade->units()
            ->active()
            ->ordered()
            ->orderBy('id')
            ->withCount(['topics' => fn (Builder $query) => $query->published()])
            ->get();

        $selectedUnit = $units->firstWhere('code', $request->query('unite')) ?? $units->first();

        $topics = $selectedUnit?->topics()
            ->published()
            ->ordered()
            ->orderBy('id')
            ->withCount([
                'learningObjectives' => fn (Builder $query) => $query->active(),
                'theoryArticles' => fn (Builder $query) => $query->published(),
            ])
            ->get() ?? collect();

        return view('student.curriculum.grade', [
            'program' => $program,
            'grades' => $grades,
            'grade' => $grade,
            'units' => $units,
            'selectedUnit' => $selectedUnit,
            'topics' => $topics,
        ]);
    }

    /**
     * A topic's learning objectives with their MEB code and Bloom level.
     */
    public function topic(string $program, int $grade, string $unit, string $topic): View
    {
        $program = $this->findProgram($program);

        /** @var Grade $grade */
        $grade = $program->grades()->active()->where('number', $grade)->firstOrFail();

        /** @var Unit $unit */
        $unit = $grade->units()->active()->where('code', $unit)->firstOrFail();

        /** @var Topic $topic */
        $topic = $unit->topics()->published()->where('slug', $topic)->firstOrFail();

        // Same numbering as the grade page: position within the ordered lists.
        $unitPosition = $grade->units()->active()->ordered()->orderBy('id')->pluck('id')->search($unit->id) + 1;
        $topicPosition = $unit->topics()->published()->ordered()->orderBy('id')->pluck('id')->search($topic->id) + 1;

        return view('student.curriculum.topic', [
            'program' => $program,
            'grade' => $grade,
            'unit' => $unit,
            'topic' => $topic,
            'objectives' => $topic->learningObjectives()->active()->ordered()->get(),
            'articles' => $topic->theoryArticles()->published()->ordered()->orderBy('id')->get(),
            'unitPosition' => $unitPosition,
            'topicNumber' => "{$unitPosition}.{$topicPosition}",
        ]);
    }

    private function findProgram(string $slug): Program
    {
        return Program::published()->where('slug', $slug)->firstOrFail();
    }
}
