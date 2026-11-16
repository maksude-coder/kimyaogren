<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MasteryLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's mastery of one topic: accuracy on their last answers in it
 * (Sprint 3.3 plan § 2). Always recomputed from `question_attempts`.
 */
class TopicMastery extends Model
{
    use HasFactory;

    /** Mastery looks at this many of the student's latest answers in the topic. */
    public const WINDOW = 20;

    protected $fillable = [
        'user_id',
        'topic_id',
        'mastery_percentage',
        'mastery_level',
        'total_attempts',
        'correct_attempts',
        'last_practiced_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'topic_id' => 'integer',
        'mastery_percentage' => 'decimal:2',
        'mastery_level' => MasteryLevel::class,
        'total_attempts' => 'integer',
        'correct_attempts' => 'integer',
        'last_practiced_at' => 'datetime',
        'next_review_at' => 'datetime',
        'ease_factor' => 'decimal:2',
        'interval_days' => 'integer',
        'repetitions' => 'integer',
        'is_due' => 'boolean',
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

    // Calculation

    /**
     * Recompute the student's mastery of a topic from their answers (rules 1–5).
     * Returns null, and stores nothing, when the student has no answer in the topic.
     */
    public static function refreshFor(int $userId, int $topicId): ?self
    {
        // Deprecated and deleted questions still count: the answers were given.
        $attempts = QuestionAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('question_id', Question::withTrashed()->where('topic_id', $topicId)->select('id'));

        $total = (clone $attempts)->count();

        if ($total === 0) {
            return null;
        }

        $window = (clone $attempts)
            ->orderByDesc('answered_at')
            ->orderByDesc('id')
            ->limit(self::WINDOW)
            ->pluck('is_correct');

        $percentage = $window->filter()->count() / $window->count() * 100;

        return self::updateOrCreate(
            ['user_id' => $userId, 'topic_id' => $topicId],
            [
                'mastery_percentage' => round($percentage, 2),
                'mastery_level' => MasteryLevel::fromAccuracy($percentage, $window->count()),
                'total_attempts' => $total,
                'correct_attempts' => (clone $attempts)->where('is_correct', true)->count(),
                'last_practiced_at' => (clone $attempts)->max('answered_at'),
            ],
        );
    }

    // Display

    public function hasEnoughData(): bool
    {
        return $this->mastery_level !== MasteryLevel::InsufficientData;
    }

    /**
     * Whole-number percentage for display; null while there is not enough data.
     */
    public function displayPercentage(): ?int
    {
        return $this->hasEnoughData() ? (int) round((float) $this->mastery_percentage) : null;
    }
}
