<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PracticeSessionType;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A student's practice session (Sprint 3.2 plan § 2). Its questions are
 * fixed at start in `session_data.question_ids`; answers are
 * `QuestionAttempt` rows; unanswered questions count as blank at the end.
 */
class PracticeSession extends Model
{
    use HasFactory;

    /** Session sizes offered on the topic page (design § 04). */
    public const SIZES = [10, 20, 40];

    /** Target and time allowance per question, in seconds (design § 04, § 09). */
    public const SECONDS_PER_QUESTION = 90;

    protected $fillable = [
        'user_id',
        'topic_id',
        'type',
        'started_at',
        'completed_at',
        'time_limit_seconds',
        'question_count',
        'correct_count',
        'skipped_count',
        'score',
        'session_data',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'topic_id' => 'integer',
        'type' => PracticeSessionType::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_limit_seconds' => 'integer',
        'question_count' => 'integer',
        'correct_count' => 'integer',
        'skipped_count' => 'integer',
        'score' => 'decimal:2',
        'session_data' => 'array',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    // Scopes

    public function scopeOpen($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    // Starting

    /**
     * Start a topic session, or return the student's open one for the topic.
     *
     * Questions never answered by the student come first, then those last
     * answered wrong, then the rest; random within each group (rule 3).
     *
     * @throws DomainException when the topic has no approved question
     */
    public static function startForTopic(User $user, Topic $topic, int $size, bool $timed): self
    {
        $open = self::query()->where('user_id', $user->id)->where('topic_id', $topic->id)->open()->latest('id')->first();

        if ($open !== null && ! $open->finishIfExpired()) {
            return $open;
        }

        $lastResults = QuestionAttempt::query()
            ->where('user_id', $user->id)
            ->whereIn('question_id', $topic->questions()->approved()->select('id'))
            ->orderBy('answered_at')
            ->orderBy('id')
            ->pluck('is_correct', 'question_id');

        $questionIds = $topic->questions()->approved()->pluck('id')
            ->shuffle()
            ->sortBy(fn (int $id): int => match (true) {
                ! $lastResults->has($id) => 0,
                ! $lastResults->get($id) => 1,
                default => 2,
            })
            ->take($size)
            ->values()
            ->all();

        if ($questionIds === []) {
            throw new DomainException('The topic has no approved questions.');
        }

        return self::create([
            'user_id' => $user->id,
            'topic_id' => $topic->id,
            'type' => PracticeSessionType::Topic,
            'started_at' => Carbon::now(),
            'time_limit_seconds' => $timed ? count($questionIds) * self::SECONDS_PER_QUESTION : null,
            'question_count' => count($questionIds),
            'session_data' => [
                'question_ids' => $questionIds,
                'flagged' => [],
                'mastery_before' => self::masteryOf($user->id, $topic->id),
            ],
        ]);
    }

    /**
     * The student's displayed mastery of a topic right now; null without enough data.
     */
    private static function masteryOf(int $userId, ?int $topicId): ?int
    {
        return $topicId === null
            ? null
            : TopicMastery::query()->where('user_id', $userId)->where('topic_id', $topicId)->first()?->displayPercentage();
    }

    /**
     * Topic mastery when the session started and when it was completed
     * (Sprint 3.3 plan § 2, rule 11); each null without enough data.
     *
     * @return array{before: int|null, after: int|null}
     */
    public function masteryChange(): array
    {
        return [
            'before' => $this->session_data['mastery_before'] ?? null,
            'after' => $this->session_data['mastery_after'] ?? null,
        ];
    }

    // Questions

    /**
     * @return list<int>
     */
    public function questionIds(): array
    {
        return array_map('intval', $this->session_data['question_ids'] ?? []);
    }

    /**
     * @return list<int>
     */
    public function flaggedIds(): array
    {
        return array_map('intval', $this->session_data['flagged'] ?? []);
    }

    /**
     * The session's questions in their fixed order, with options and objective.
     *
     * @return Collection<int, Question>
     */
    public function questions(): Collection
    {
        $order = array_flip($this->questionIds());

        return Question::withTrashed()
            ->with(['options', 'learningObjective'])
            ->findMany($this->questionIds())
            ->sortBy(fn (Question $question): int => $order[$question->id])
            ->values();
    }

    /**
     * The question at a 1-based position, or null.
     */
    public function questionAt(int $number): ?Question
    {
        $id = $this->questionIds()[$number - 1] ?? null;

        return $id === null ? null : Question::withTrashed()->with(['options', 'learningObjective'])->find($id);
    }

    /**
     * 1-based position of the first unanswered question, or null when all are answered.
     */
    public function firstUnansweredNumber(): ?int
    {
        $answered = $this->attempts()->pluck('question_id')->all();

        foreach ($this->questionIds() as $index => $id) {
            if (! in_array($id, $answered, true)) {
                return $index + 1;
            }
        }

        return null;
    }

    public function toggleFlag(Question $question): void
    {
        $flagged = $this->flaggedIds();

        $flagged = in_array($question->id, $flagged, true)
            ? array_values(array_diff($flagged, [$question->id]))
            : [...$flagged, $question->id];

        $this->session_data = [...($this->session_data ?? []), 'flagged' => $flagged];
        $this->save();
    }

    // Answering

    /**
     * Record the student's answer; a question is answered once per session (rule 5).
     *
     * @throws DomainException when the session is over, or the question or option is not part of it
     */
    public function answer(Question $question, QuestionOption $option, ?int $timeSpentSeconds = null): QuestionAttempt
    {
        if ($this->isCompleted()) {
            throw new DomainException('The session is already completed.');
        }

        if (! in_array($question->id, $this->questionIds(), true) || $option->question_id !== $question->id) {
            throw new DomainException('The answer does not belong to this session.');
        }

        $attempt = $this->attempts()->firstOrCreate(
            ['question_id' => $question->id, 'attempt_number' => 1],
            [
                'user_id' => $this->user_id,
                'selected_option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'time_spent_seconds' => $timeSpentSeconds === null ? null : max(0, min($timeSpentSeconds, 65535)),
                'answered_at' => Carbon::now(),
            ],
        );

        // Every new answer moves the student's mastery of the topic (Sprint 3.3 plan § 2, rule 4).
        if ($attempt->wasRecentlyCreated) {
            TopicMastery::refreshFor($this->user_id, $question->topic_id);
        }

        return $attempt;
    }

    // Timer and completion

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isTimed(): bool
    {
        return $this->time_limit_seconds !== null;
    }

    public function expiresAt(): ?Carbon
    {
        return $this->isTimed() ? $this->started_at->copy()->addSeconds($this->time_limit_seconds) : null;
    }

    /**
     * Seconds left on the countdown; null for an untimed session.
     */
    public function secondsRemaining(): ?int
    {
        if (! $this->isTimed()) {
            return null;
        }

        return max(0, $this->expiresAt()->getTimestamp() - Carbon::now()->getTimestamp());
    }

    /**
     * Complete the session if its time is up (rule 8). Returns whether it is now completed.
     */
    public function finishIfExpired(): bool
    {
        if (! $this->isCompleted() && $this->isTimed() && Carbon::now()->greaterThanOrEqualTo($this->expiresAt())) {
            $this->complete($this->expiresAt());
        }

        return $this->isCompleted();
    }

    /**
     * Close the session and store its totals (rule 9). Idempotent.
     */
    public function complete(?Carbon $at = null): void
    {
        if ($this->isCompleted()) {
            return;
        }

        $answered = $this->attempts()->count();
        $correct = $this->attempts()->where('is_correct', true)->count();

        $this->update([
            'completed_at' => $at ?? Carbon::now(),
            'correct_count' => $correct,
            'skipped_count' => max(0, $this->question_count - $answered),
            'score' => $this->question_count > 0 ? round($correct / $this->question_count * 100, 2) : 0,
            'session_data' => [...($this->session_data ?? []), 'mastery_after' => self::masteryOf($this->user_id, $this->topic_id)],
        ]);
    }

    /**
     * Sizes the topic page offers for a topic with the given number of
     * approved questions: every size the topic can fill, plus one "all"
     * choice when it cannot fill the next one (design deviation, plan § 4).
     *
     * @return list<array{size: int, count: int}>
     */
    public static function sizeChoices(int $available): array
    {
        $choices = [];

        foreach (self::SIZES as $size) {
            if ($available <= 0) {
                break;
            }

            $choices[] = ['size' => $size, 'count' => min($size, $available)];

            if ($size >= $available) {
                break;
            }
        }

        return $choices;
    }

    /**
     * "13:40" style minutes:seconds.
     */
    public static function formatDuration(int $seconds): string
    {
        return sprintf('%d:%02d', intdiv(max(0, $seconds), 60), max(0, $seconds) % 60);
    }

    public function wrongCount(): int
    {
        return $this->question_count - $this->correct_count - $this->skipped_count;
    }

    /**
     * Seconds from start to completion (or to now while open).
     */
    public function durationSeconds(): int
    {
        return max(0, ($this->completed_at ?? Carbon::now())->getTimestamp() - $this->started_at->getTimestamp());
    }
}
