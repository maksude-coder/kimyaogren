<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MasteryLevel;
use App\Models\Topic;
use App\Models\TopicMastery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopicMastery>
 */
class TopicMasteryFactory extends Factory
{
    protected $model = TopicMastery::class;

    /**
     * A row without answers; tests normally get rows through
     * `TopicMastery::refreshFor()` or `accuracy()`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'topic_id' => Topic::factory(),
            'mastery_percentage' => 0,
            'mastery_level' => MasteryLevel::InsufficientData->value,
            'total_attempts' => 0,
            'correct_attempts' => 0,
        ];
    }

    /**
     * A row for `$correct` right answers out of `$total` (all inside the window).
     */
    public function accuracy(int $correct, int $total): static
    {
        return $this->state(fn (array $attributes) => [
            'mastery_percentage' => $total > 0 ? round($correct / $total * 100, 2) : 0,
            'mastery_level' => MasteryLevel::fromAccuracy($total > 0 ? $correct / $total * 100 : 0, $total)->value,
            'total_attempts' => $total,
            'correct_attempts' => $correct,
            'last_practiced_at' => now(),
        ]);
    }
}
