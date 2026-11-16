<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable record of one answer (dd-question-attempts.md): no
 * `created_at` / `updated_at`, only `answered_at`.
 */
class QuestionAttempt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'question_id',
        'practice_session_id',
        'selected_option_id',
        'answer_text',
        'is_correct',
        'quality',
        'time_spent_seconds',
        'attempt_number',
        'metadata',
        'answered_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'question_id' => 'integer',
        'practice_session_id' => 'integer',
        'selected_option_id' => 'integer',
        'is_correct' => 'boolean',
        'quality' => 'integer',
        'time_spent_seconds' => 'integer',
        'attempt_number' => 'integer',
        'metadata' => 'array',
        'answered_at' => 'datetime',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }
}
