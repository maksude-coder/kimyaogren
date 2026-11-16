<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BloomLevel;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Enums\YksSession;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'topic_id',
        'learning_objective_id',
        'type',
        'content',
        'explanation',
        'difficulty',
        'bloom_level',
        'is_active',
        'yks_year',
        'yks_session',
        'source',
        'tags',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'topic_id' => 'integer',
        'learning_objective_id' => 'integer',
        'type' => QuestionType::class,
        'difficulty' => QuestionDifficulty::class,
        'bloom_level' => BloomLevel::class,
        'status' => QuestionStatus::class,
        'is_active' => 'boolean',
        'yks_year' => 'integer',
        'yks_session' => YksSession::class,
        'tags' => 'array',
    ];

    protected $attributes = [
        'status' => QuestionStatus::Draft->value,
    ];

    // Relationships

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function correctOption(): HasOne
    {
        return $this->hasOne(QuestionOption::class)->where('is_correct', true);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Questions students may see: Approved and active (dd-questions.md rule 9).
     */
    public function scopeApproved($query)
    {
        return $query->where('status', QuestionStatus::Approved)
            ->where('is_active', true);
    }

    // Options

    /**
     * Replace the question's options with the given rows, in order.
     *
     * Existing rows are updated in place by position, so an option keeps
     * its id when the question is edited (Sprint 3.1 plan § 2, rule 7).
     *
     * @param  list<array{option_text: string, is_correct: bool, feedback?: string|null}>  $rows
     */
    public function syncOptions(array $rows): void
    {
        $this->getConnection()->transaction(function () use ($rows): void {
            $existing = $this->options()->get()->values();

            foreach (array_values($rows) as $index => $row) {
                $attributes = [
                    'option_text' => $row['option_text'],
                    'is_correct' => $row['is_correct'],
                    'feedback' => $row['feedback'] ?? null,
                    'order' => $index + 1,
                ];

                if ($option = $existing->get($index)) {
                    $option->update($attributes);
                } else {
                    $this->options()->create($attributes);
                }
            }

            $existing->slice(count($rows))->each->delete();
        });

        $this->unsetRelation('options');
        $this->unsetRelation('correctOption');
    }

    /**
     * Whether the options satisfy the type's rule: the fixed number of
     * options with exactly one correct (multiple choice 5, true/false 2).
     */
    public function hasValidOptions(): bool
    {
        $expected = $this->type->optionCount();
        $options = $this->options()->get();

        return $expected !== null
            && $options->count() === $expected
            && $options->where('is_correct', true)->count() === 1;
    }

    public function hasExplanation(): bool
    {
        return trim(strip_tags((string) $this->explanation)) !== '';
    }

    // Review workflow (Sprint 3.1 plan § 2, rule 5)

    /**
     * @throws DomainException
     */
    public function submitForReview(): void
    {
        $this->transitionTo(QuestionStatus::Review);
    }

    /**
     * @throws DomainException
     */
    public function requestChanges(): void
    {
        $this->transitionTo(QuestionStatus::Draft);
    }

    /**
     * @throws DomainException when the solution is missing or the options are invalid
     */
    public function approve(): void
    {
        if (! $this->hasExplanation()) {
            throw new DomainException('A question needs a step-by-step solution before it can be approved.');
        }

        if (! $this->hasValidOptions()) {
            throw new DomainException('The options are incomplete: the question needs all of its options and exactly one correct answer.');
        }

        $this->transitionTo(QuestionStatus::Approved);
    }

    /**
     * @throws DomainException
     */
    public function deprecate(): void
    {
        $this->transitionTo(QuestionStatus::Deprecated);
    }

    /**
     * @throws DomainException
     */
    private function transitionTo(QuestionStatus $status): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new DomainException("A {$this->status->label()} question cannot become {$status->label()}.");
        }

        $this->status = $status;
        $this->save();
    }
}
