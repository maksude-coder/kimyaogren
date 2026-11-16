<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    use HasFactory;

    /** Display letters of a multiple-choice question, by `order` 1–5. */
    public const LETTERS = ['A', 'B', 'C', 'D', 'E'];

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'order',
        'left_item',
        'right_item',
        'feedback',
    ];

    protected $casts = [
        'question_id' => 'integer',
        'is_correct' => 'boolean',
        'order' => 'integer',
    ];

    // Relationships

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * The option's letter (A–E) from its 1-based `order`.
     */
    public function letter(): ?string
    {
        return self::LETTERS[$this->order - 1] ?? null;
    }
}
