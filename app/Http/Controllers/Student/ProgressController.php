<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Enums\MasteryLevel;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\Program;
use App\Models\QuestionAttempt;
use App\Models\Topic;
use App\Models\TopicMastery;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The student's progress panel (design § 06, Sprint 3.3 plan § 2): topic
 * mastery, weak objectives and the 28-day calendar.
 */
class ProgressController extends Controller
{
    private const CALENDAR_DAYS = 28;

    private const WEAK_OBJECTIVES_SHOWN = 5;

    /** Short weekday names, indexed by Carbon's dayOfWeek (0 = Sunday). */
    private const WEEKDAYS = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $program = Program::published()->ordered()->first();
        $grades = $program?->grades()->active()->ordered()->get() ?? collect();

        // The student's own grade first, otherwise the first one (rule 8).
        $grade = $grades->firstWhere('number', (int) $request->query('sinif'))
            ?? $grades->firstWhere('id', $user->grade_id)
            ?? $grades->first();

        $totalAnswers = $user->questionAttempts()->count();
        $correctAnswers = $user->questionAttempts()->where('is_correct', true)->count();

        return view('student.progress.index', [
            'program' => $program,
            'grades' => $grades,
            'grade' => $grade,
            'totalAnswers' => $totalAnswers,
            'accuracy' => $totalAnswers > 0 ? (int) round($correctAnswers / $totalAnswers * 100) : null,
            'topicsStarted' => $program === null ? 0 : $user->topicMasteries()->whereIn('topic_id', $this->visibleTopics($program)->select('topics.id'))->count(),
            'topicsTotal' => $program === null ? 0 : $this->visibleTopics($program)->count(),
            'masteries' => $grade === null ? collect() : $this->masteries($user, $program, $grade),
            'weakObjectives' => $program === null ? [] : $this->weakObjectives($user, $program),
            'calendar' => $this->calendar($user),
        ]);
    }

    /**
     * Topics the curriculum browser shows for the program.
     *
     * @return Builder<Topic>
     */
    private function visibleTopics(Program $program, ?Grade $grade = null): Builder
    {
        return Topic::published()
            ->whereHas('unit', fn (Builder $unit) => $unit->active()
                ->whereHas('grade', fn (Builder $query) => $query->active()
                    ->where('program_id', $program->id)
                    ->when($grade !== null, fn (Builder $query) => $query->whereKey($grade->id))));
    }

    /**
     * The student's mastery rows for a grade, in curriculum order, each with its topic URL (rule 8).
     *
     * @return Collection<int, array{mastery: TopicMastery, url: string}>
     */
    private function masteries(User $user, Program $program, Grade $grade): Collection
    {
        return $user->topicMasteries()
            ->whereIn('topic_id', $this->visibleTopics($program, $grade)->select('topics.id'))
            ->with('topic.unit')
            ->get()
            ->sortBy(fn (TopicMastery $mastery): array => [
                $mastery->topic->unit->order,
                $mastery->topic->unit->id,
                $mastery->topic->order,
                $mastery->topic->id,
            ])
            ->values()
            ->map(fn (TopicMastery $mastery): array => [
                'mastery' => $mastery,
                'url' => route('curriculum.topic', [
                    'program' => $program->slug,
                    'grade' => $grade->number,
                    'unit' => $mastery->topic->unit->code,
                    'topic' => $mastery->topic->slug,
                ]),
            ]);
    }

    /**
     * Objectives with at least the minimum number of answers whose last
     * answers are less than half correct; weakest first (rule 9).
     *
     * @return list<array{code: string|null, description: string, correct: int, total: int, url: string}>
     */
    private function weakObjectives(User $user, Program $program): array
    {
        $weak = QuestionAttempt::query()
            ->join('questions', 'questions.id', '=', 'question_attempts.question_id')
            ->where('question_attempts.user_id', $user->id)
            ->whereNotNull('questions.learning_objective_id')
            ->orderByDesc('question_attempts.answered_at')
            ->orderByDesc('question_attempts.id')
            ->get(['questions.learning_objective_id', 'question_attempts.is_correct'])
            ->groupBy('learning_objective_id')
            ->map(function (Collection $attempts): array {
                $window = $attempts->take(TopicMastery::WINDOW);

                return ['correct' => $window->where('is_correct', true)->count(), 'total' => $window->count()];
            })
            ->filter(fn (array $row): bool => $row['total'] >= MasteryLevel::MINIMUM_ATTEMPTS && $row['total'] > $row['correct'] * 2);

        if ($weak->isEmpty()) {
            return [];
        }

        return LearningObjective::query()
            ->active()
            ->whereKey($weak->keys())
            ->whereIn('topic_id', $this->visibleTopics($program)->select('topics.id'))
            ->with('topic.unit.grade')
            ->get()
            ->map(fn (LearningObjective $objective): array => [
                'code' => $objective->code,
                'description' => $objective->description,
                'correct' => $weak[$objective->id]['correct'],
                'total' => $weak[$objective->id]['total'],
                'url' => route('curriculum.topic', [
                    'program' => $program->slug,
                    'grade' => $objective->topic->unit->grade->number,
                    'unit' => $objective->topic->unit->code,
                    'topic' => $objective->topic->slug,
                ]),
            ])
            ->sortBy([
                fn (array $a, array $b): int => $a['correct'] / $a['total'] <=> $b['correct'] / $b['total'],
                fn (array $a, array $b): int => $b['total'] <=> $a['total'],
            ])
            ->take(self::WEAK_OBJECTIVES_SHOWN)
            ->values()
            ->all();
    }

    /**
     * The last 28 days ending today; a day is filled when an answer was given on it (rule 10).
     *
     * @return array{days: list<array{date: string, weekday: string, filled: bool, today: bool}>, filled: int}
     */
    private function calendar(User $user): array
    {
        // Days are counted in the students' time zone, whatever the server's (rule 10).
        $timezone = config('app.student_timezone');
        $today = Carbon::now($timezone)->startOfDay();
        $first = $today->copy()->subDays(self::CALENDAR_DAYS - 1);

        $studied = $user->questionAttempts()
            ->where('answered_at', '>=', $first->copy()->utc())
            ->pluck('answered_at')
            ->map(fn (Carbon $answeredAt): string => $answeredAt->copy()->setTimezone($timezone)->toDateString())
            ->unique()
            ->flip();

        $days = [];

        for ($day = $first->copy(); $day->lessThanOrEqualTo($today); $day->addDay()) {
            $days[] = [
                'date' => $day->toDateString(),
                'weekday' => self::WEEKDAYS[$day->dayOfWeek],
                'filled' => $studied->has($day->toDateString()),
                'today' => $day->equalTo($today),
            ];
        }

        return ['days' => $days, 'filled' => count(array_filter($days, fn (array $day): bool => $day['filled']))];
    }
}
