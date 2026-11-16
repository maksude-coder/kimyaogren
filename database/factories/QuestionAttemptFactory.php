<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionAttempt>
 */
class QuestionAttemptFactory extends Factory
{
    protected $model = QuestionAttempt::class;

    /**
     * A standalone correct attempt (no session, no option).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'question_id' => Question::factory(),
            'is_correct' => true,
            'attempt_number' => 1,
            'answered_at' => now(),
        ];
    }

    public function wrong(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => false,
        ]);
    }
}
