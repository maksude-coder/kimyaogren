<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    /**
     * A draft multiple-choice question without options; add `withOptions()`
     * for a complete one.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_id' => Topic::factory(),
            'type' => QuestionType::MultipleChoice->value,
            'content' => '<p>'.$this->faker->sentence(12).'</p>',
            'explanation' => '<p>'.$this->faker->sentence(16).'</p>',
            'difficulty' => $this->faker->numberBetween(1, 5),
            'bloom_level' => $this->faker->numberBetween(1, 6),
            'is_active' => true,
        ];
    }

    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::TrueFalse->value,
        ]);
    }

    public function withoutExplanation(): static
    {
        return $this->state(fn (array $attributes) => [
            'explanation' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function inReview(): static
    {
        return $this->status(QuestionStatus::Review);
    }

    public function approved(): static
    {
        return $this->status(QuestionStatus::Approved);
    }

    public function deprecated(): static
    {
        return $this->status(QuestionStatus::Deprecated);
    }

    /**
     * Create the type's full set of options with the first one correct.
     */
    public function withOptions(): static
    {
        return $this->afterCreating(function (Question $question): void {
            $texts = $question->type === QuestionType::TrueFalse
                ? ['Doğru', 'Yanlış']
                : array_map(fn (int $n): string => "Option {$n}", range(1, 5));

            $question->syncOptions(array_map(
                fn (string $text, int $index): array => ['option_text' => $text, 'is_correct' => $index === 0],
                $texts,
                array_keys($texts),
            ));
        });
    }

    /**
     * `status` is not mass assignable: it only changes through the workflow.
     */
    private function status(QuestionStatus $status): static
    {
        return $this->afterMaking(function (Question $question) use ($status): void {
            $question->status = $status;
        });
    }
}
