<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PracticeSession;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Topic;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Topic practice (design § 05, § 09; Sprint 3.2 plan § 2): start a session,
 * answer its questions one by one, finish, read the summary.
 *
 * A session is only ever reachable by its own student; anything else is a 404.
 */
class PracticeController extends Controller
{
    /**
     * Landing point for guests: after login they return to the topic page,
     * where the "Soru çöz" panel is.
     */
    public function intent(int $topic): RedirectResponse
    {
        return redirect()->to($this->topicUrl($this->findTopic($topic)));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topic_id' => ['required', 'integer'],
            'size' => ['required', 'integer', Rule::in(PracticeSession::SIZES)],
            'timed' => ['nullable', 'boolean'],
        ]);

        $topic = $this->findTopic((int) $validated['topic_id']);

        try {
            $session = PracticeSession::startForTopic($request->user(), $topic, (int) $validated['size'], $request->boolean('timed'));
        } catch (DomainException) {
            return redirect()->to($this->topicUrl($topic))->with('status', 'Bu konunun soruları hazırlanıyor.');
        }

        return redirect()->route('practice.show', $session->id);
    }

    public function show(Request $request, int $session, ?int $number = null): View|RedirectResponse
    {
        $session = $this->findSession($request, $session);

        if ($session->finishIfExpired()) {
            return redirect()->route('practice.summary', $session->id);
        }

        $number ??= $session->firstUnansweredNumber() ?? 1;
        $question = $session->questionAt($number) ?? abort(404);
        $attempts = $session->attempts()->get()->keyBy('question_id');

        // Time per question is measured from the first time it is shown (rule 10).
        $request->session()->put("practice.{$session->id}.shown.{$question->id}", $request->session()->get("practice.{$session->id}.shown.{$question->id}", now()->timestamp));

        return view('student.practice.show', [
            'session' => $session,
            'topic' => $session->topic,
            'number' => $number,
            'question' => $question,
            'attempt' => $attempts->get($question->id),
            'grid' => $this->grid($session, $attempts),
            'isFlagged' => in_array($question->id, $session->flaggedIds(), true),
            'nextNumber' => $this->nextOpenNumber($session, $attempts, $number),
            'answeredCount' => $attempts->count(),
        ]);
    }

    public function answer(Request $request, int $session, int $number): RedirectResponse
    {
        $session = $this->findSession($request, $session);

        if ($session->finishIfExpired()) {
            return redirect()->route('practice.summary', $session->id);
        }

        $question = $session->questionAt($number) ?? abort(404);

        $validated = $request->validate([
            'option' => ['required', 'integer', Rule::in($question->options->modelKeys())],
        ], [
            'option.required' => 'Bir şık seç ya da soruyu boş bırak.',
            'option.in' => 'Bir şık seç ya da soruyu boş bırak.',
        ]);

        $shownAt = $request->session()->pull("practice.{$session->id}.shown.{$question->id}");

        $session->answer(
            $question,
            $question->options->find((int) $validated['option']),
            $shownAt === null ? null : now()->timestamp - (int) $shownAt,
        );

        return redirect()->route('practice.show', [$session->id, $number]);
    }

    public function flag(Request $request, int $session, int $number): RedirectResponse
    {
        $session = $this->findSession($request, $session);

        if ($session->finishIfExpired()) {
            return redirect()->route('practice.summary', $session->id);
        }

        $session->toggleFlag($session->questionAt($number) ?? abort(404));

        return redirect()->route('practice.show', [$session->id, $number]);
    }

    public function finish(Request $request, int $session): RedirectResponse
    {
        $session = $this->findSession($request, $session);

        if (! $session->finishIfExpired()) {
            $session->complete();
        }

        $request->session()->forget("practice.{$session->id}");

        return redirect()->route('practice.summary', $session->id);
    }

    public function summary(Request $request, int $session): View|RedirectResponse
    {
        $session = $this->findSession($request, $session);

        if (! $session->finishIfExpired()) {
            return redirect()->route('practice.show', $session->id);
        }

        $questions = $session->questions();
        $attempts = $session->attempts()->with('selectedOption')->get()->keyBy('question_id');

        $rows = $questions->map(fn (Question $question, int $index): array => [
            'number' => $index + 1,
            'question' => $question,
            'attempt' => $attempts->get($question->id),
            'correctOption' => $question->options->firstWhere('is_correct', true),
        ]);

        return view('student.practice.summary', [
            'session' => $session,
            'topic' => $session->topic,
            'topicUrl' => $session->topic === null ? route('curriculum.index') : $this->topicUrl($session->topic),
            'rows' => $rows,
            'missed' => $rows->filter(fn (array $row): bool => ! $row['attempt']?->is_correct)->values(),
            'objectives' => $this->objectiveBreakdown($rows),
            'averageSeconds' => $session->question_count > 0 ? intdiv($session->durationSeconds(), $session->question_count) : 0,
        ]);
    }

    /**
     * State of every question for the grid: correct, wrong, flagged or open.
     *
     * @param  Collection<int, QuestionAttempt>  $attempts
     * @return list<array{number: int, state: string, flagged: bool}>
     */
    private function grid(PracticeSession $session, Collection $attempts): array
    {
        $flagged = $session->flaggedIds();

        return array_map(fn (int $id, int $index): array => [
            'number' => $index + 1,
            'state' => match ($attempts->get($id)?->is_correct) {
                true => 'correct',
                false => 'wrong',
                default => 'open',
            },
            'flagged' => in_array($id, $flagged, true),
        ], $session->questionIds(), array_keys($session->questionIds()));
    }

    /**
     * The next unanswered question after the given one, wrapping around; null when none is left.
     *
     * @param  Collection<int, QuestionAttempt>  $attempts
     */
    private function nextOpenNumber(PracticeSession $session, Collection $attempts, int $current): ?int
    {
        $ids = $session->questionIds();
        $count = count($ids);

        for ($step = 1; $step < $count; $step++) {
            $index = ($current - 1 + $step) % $count;

            if (! $attempts->has($ids[$index])) {
                return $index + 1;
            }
        }

        return null;
    }

    /**
     * Correct / total per learning objective, in the order objectives first appear.
     *
     * @param  Collection<int, array{number: int, question: Question, attempt: QuestionAttempt|null}>  $rows
     * @return list<array{code: string|null, description: string, correct: int, total: int}>
     */
    private function objectiveBreakdown(Collection $rows): array
    {
        return $rows
            ->groupBy(fn (array $row): int => $row['question']->learning_objective_id ?? 0)
            ->map(fn (Collection $group): array => [
                'code' => $group->first()['question']->learningObjective?->code,
                'description' => $group->first()['question']->learningObjective?->description ?? 'Kazanım belirtilmemiş',
                'correct' => $group->filter(fn (array $row): bool => (bool) $row['attempt']?->is_correct)->count(),
                'total' => $group->count(),
            ])
            ->values()
            ->all();
    }

    private function findSession(Request $request, int $id): PracticeSession
    {
        return $request->user()->practiceSessions()->with('topic.unit.grade.program')->findOrFail($id);
    }

    /**
     * Only topics the curriculum browser shows can be practised (rule 2).
     */
    private function findTopic(int $id): Topic
    {
        return Topic::published()
            ->whereHas('unit', fn (Builder $unit) => $unit->active()
                ->whereHas('grade', fn (Builder $grade) => $grade->active()
                    ->whereHas('program', fn (Builder $program) => $program->published())))
            ->with('unit.grade.program')
            ->findOrFail($id);
    }

    private function topicUrl(Topic $topic): string
    {
        return route('curriculum.topic', [
            'program' => $topic->unit->grade->program->slug,
            'grade' => $topic->unit->grade->number,
            'unit' => $topic->unit->code,
            'topic' => $topic->slug,
        ]);
    }
}
