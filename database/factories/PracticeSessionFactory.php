<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PracticeSessionType;
use App\Models\PracticeSession;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticeSession>
 */
class PracticeSessionFactory extends Factory
{
    protected $model = PracticeSession::class;

    /**
     * An open, untimed topic session without questions; tests normally
     * build sessions through `PracticeSession::startForTopic()`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'topic_id' => Topic::factory(),
            'type' => PracticeSessionType::Topic->value,
            'started_at' => now(),
            'question_count' => 0,
            'session_data' => ['question_ids' => [], 'flagged' => []],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
            'score' => 0,
        ]);
    }
}
