<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Enums\MasteryLevel;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\PracticeSession;
use App\Models\Program;
use App\Models\Topic;
use App\Models\TopicMastery;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The student's "Bugün" home page (design § 02, Sprint 3.4 plan § 2): one
 * recommended thing to do, with its reason.
 */
class TodayController extends Controller
{
    /** Questions in the session a recommendation starts (rule 3). */
    public const RECOMMENDED_SIZE = 10;

    private const WEAK_TOPICS_SHOWN = 3;

    private const WEEK_DAYS = 7;

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $now = Carbon::now(config('app.student_timezone'));
        $program = Program::published()->ordered()->first();
        $grades = $program?->grades()->active()->ordered()->get() ?? collect();

        // The student's own grade, otherwise the first one (rules 1 and 2d).
        $grade = $grades->firstWhere('id', $user->grade_id) ?? $grades->first();

        $openSession = $this->openSession($user);
        $isNewStudent = ! $user->questionAttempts()->exists();

        $masteries = $program === null || $isNewStudent
            ? collect()
            : $user->topicMasteries()
                ->whereIn('topic_id', $this->visibleTopics($program)->select('topics.id'))
                ->with('topic.unit.grade')
                ->get();

        $recommendation = $program === null || $isNewStudent
            ? null
            : $this->recommendation($program, $grade, $masteries, $openSession, $now);

        return view('student.today', [
            'program' => $program,
            'grades' => $grades,
            'grade' => $grade,
            'greeting' => $this->greeting($now),
            'firstName' => explode(' ', trim($user->name))[0],
            'date' => $now->translatedFormat('j F l'),
            'isNewStudent' => $isNewStudent,
            'recommendation' => $recommendation,
            // Not shown twice when the recommendation already continues it (rule 4).
            'openSession' => $openSession !== null && $recommendation?->session?->is($openSession) ? null : $openSession,
            'openSessionAnswered' => $openSession?->attempts()->count() ?? 0,
            'weakTopics' => $program === null ? collect() : $this->weakTopics($program, $masteries),
            'week' => $this->week($user, $now),
        ]);
    }

    /**
     * Topics the curriculum browser shows for the program.
     *
     * @return Builder<Topic>
     */
    private function visibleTopics(Program $program): Builder
    {
        return Topic::published()
            ->whereHas('unit', fn (Builder $unit) => $unit->active()
                ->whereHas('grade', fn (Builder $grade) => $grade->active()->where('program_id', $program->id)));
    }

    /**
     * The latest unfinished session that has not run out of time (rule 6).
     */
    private function openSession(User $user): ?PracticeSession
    {
        return $user->practiceSessions()
            ->open()
            ->whereNotNull('topic_id')
            ->with('topic.unit.grade.program')
            ->latest('id')
            ->get()
            ->first(fn (PracticeSession $session): bool => $session->topic !== null && ! $session->finishIfExpired());
    }

    /**
     * What to do now, by the first rule that finds a topic (rule 2); null when
     * no topic of the program has an approved question (rule 5).
     *
     * @param  Collection<int, TopicMastery>  $masteries
     */
    private function recommendation(Program $program, ?Grade $grade, Collection $masteries, ?PracticeSession $openSession, Carbon $now): ?object
    {
        $questionCounts = $this->visibleTopics($program)
            ->withCount(['questions' => fn (Builder $query) => $query->approved()])
            ->get()
            ->pluck('questions_count', 'id')
            ->filter();

        $candidates = $masteries->filter(fn (TopicMastery $mastery): bool => $questionCounts->has($mastery->topic_id));

        $window = fn (TopicMastery $mastery): int => min($mastery->total_attempts, TopicMastery::WINDOW);
        $percentage = fn (TopicMastery $mastery): int => (int) round((float) $mastery->mastery_percentage);

        $lowest = fn (MasteryLevel $level): ?TopicMastery => $candidates
            ->filter(fn (TopicMastery $mastery): bool => $mastery->mastery_level === $level)
            ->sortBy(fn (TopicMastery $mastery): array => [(float) $mastery->mastery_percentage, $mastery->last_practiced_at?->getTimestamp() ?? 0])
            ->first();

        $topic = null;
        $reason = null;
        $kind = null;

        if ($mastery = $lowest(MasteryLevel::Weak)) {
            [$topic, $kind] = [$mastery->topic, 'weak'];
            $reason = "Son {$window($mastery)} soruda %{$percentage($mastery)} doğruluk. 10 soruluk kısa bir çalışma öneriyoruz.";
        } elseif ($mastery = $lowest(MasteryLevel::Developing)) {
            [$topic, $kind] = [$mastery->topic, 'developing'];
            $reason = "Son {$window($mastery)} soruda %{$percentage($mastery)} doğruluk. Yetkin düzeyi için %65 gerekiyor.";
        } elseif ($mastery = $candidates
            ->filter(fn (TopicMastery $mastery): bool => $mastery->mastery_level === MasteryLevel::InsufficientData)
            ->sortByDesc('total_attempts')
            ->first()) {
            [$topic, $kind] = [$mastery->topic, 'insufficient'];
            $reason = "Bu konuda {$mastery->total_attempts} soru çözdün. Ustalığının görünmesi için ".MasteryLevel::MINIMUM_ATTEMPTS.' soru gerekiyor.';
        } elseif ($next = $this->nextTopic($program, $grade, $questionCounts, $masteries)) {
            [$topic, $kind] = [$next, 'next'];
            $reason = 'Bu konuda henüz soru çözmedin.';
        } elseif ($mastery = $candidates->sortBy(fn (TopicMastery $mastery): int => $mastery->last_practiced_at?->getTimestamp() ?? 0)->first()) {
            [$topic, $kind] = [$mastery->topic, 'review'];
            $practised = $mastery->last_practiced_at?->copy()->setTimezone($now->getTimezone())->translatedFormat('j F');
            $reason = "Bu konuyu en son {$practised} tarihinde çalıştın.";
        }

        if ($topic === null) {
            return null;
        }

        $questions = min(self::RECOMMENDED_SIZE, (int) $questionCounts->get($topic->id));

        return (object) [
            'topic' => $topic,
            'kind' => $kind,
            'reason' => $reason,
            'questions' => $questions,
            'minutes' => (int) ceil($questions * PracticeSession::SECONDS_PER_QUESTION / 60),
            // Continue the unfinished session of the same topic instead of starting another (rule 4).
            'session' => $openSession?->topic_id === $topic->id ? $openSession : null,
        ];
    }

    /**
     * First topic of the grade, in curriculum order, the student has not answered in (rule 2d).
     *
     * @param  Collection<int, int>  $questionCounts
     * @param  Collection<int, TopicMastery>  $masteries
     */
    private function nextTopic(Program $program, ?Grade $grade, Collection $questionCounts, Collection $masteries): ?Topic
    {
        if ($grade === null) {
            return null;
        }

        return $this->visibleTopics($program)
            ->whereKey($questionCounts->keys())
            ->whereKeyNot($masteries->pluck('topic_id'))
            ->whereHas('unit', fn (Builder $unit) => $unit->where('grade_id', $grade->id))
            ->with('unit.grade')
            ->get()
            ->sortBy(fn (Topic $topic): array => [$topic->unit->order, $topic->unit->id, $topic->order, $topic->id])
            ->first();
    }

    /**
     * Topics of level Weak, lowest mastery first (rule 7).
     *
     * @param  Collection<int, TopicMastery>  $masteries
     * @return Collection<int, array{mastery: TopicMastery, answers: int, url: string}>
     */
    private function weakTopics(Program $program, Collection $masteries): Collection
    {
        return $masteries
            ->filter(fn (TopicMastery $mastery): bool => $mastery->mastery_level === MasteryLevel::Weak)
            ->sortBy(fn (TopicMastery $mastery): float => (float) $mastery->mastery_percentage)
            ->take(self::WEAK_TOPICS_SHOWN)
            ->values()
            ->map(fn (TopicMastery $mastery): array => [
                'mastery' => $mastery,
                'answers' => min($mastery->total_attempts, TopicMastery::WINDOW),
                'url' => route('curriculum.topic', [
                    'program' => $program->slug,
                    'grade' => $mastery->topic->unit->grade->number,
                    'unit' => $mastery->topic->unit->code,
                    'topic' => $mastery->topic->slug,
                ]),
            ]);
    }

    /**
     * Answers, accuracy and days studied in the last seven days ending today (rule 8).
     *
     * @return array{answers: int, accuracy: int|null, days: int, of: int}
     */
    private function week(User $user, Carbon $now): array
    {
        $first = $now->copy()->startOfDay()->subDays(self::WEEK_DAYS - 1);

        $attempts = $user->questionAttempts()
            ->where('answered_at', '>=', $first->copy()->utc())
            ->get(['is_correct', 'answered_at']);

        return [
            'answers' => $attempts->count(),
            'accuracy' => $attempts->isEmpty() ? null : (int) round($attempts->where('is_correct', true)->count() / $attempts->count() * 100),
            'days' => $attempts
                ->map(fn ($attempt): string => $attempt->answered_at->copy()->setTimezone($now->getTimezone())->toDateString())
                ->unique()
                ->count(),
            'of' => self::WEEK_DAYS,
        ];
    }

    /**
     * Greeting for the hour in the students' time zone (rule 9).
     */
    private function greeting(Carbon $now): string
    {
        return match (true) {
            $now->hour >= 5 && $now->hour < 12 => 'Günaydın',
            $now->hour >= 12 && $now->hour < 18 => 'İyi günler',
            $now->hour >= 18 && $now->hour < 22 => 'İyi akşamlar',
            default => 'İyi geceler',
        };
    }
}
