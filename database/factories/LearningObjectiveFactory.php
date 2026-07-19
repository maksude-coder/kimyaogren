<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LearningObjective;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningObjective>
 */
class LearningObjectiveFactory extends Factory
{
    protected $model = LearningObjective::class;

    /**
     * Define the model's default state.
     *
     * Business rule (dd-learning-objectives.md): `code` follows
     * `KIM.{grade}.{unit}.{topic}.{sequence}` and is unique within the
     * owning Topic, not globally (DEBT-008). `topic_id` is resolved first
     * (nested factory relationships resolve before sibling closures), so
     * the grade/unit/topic segments are read from the real, persisted
     * chain rather than guessed independently — the same class of fix
     * already applied in `GradeFactory`/`UnitFactory`/`TopicFactory`, one
     * level further down.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_id' => Topic::factory(),
            'code' => function (array $attributes) {
                $topic = Topic::with('unit.grade')->find($attributes['topic_id']);
                $unit = $topic->unit;
                $grade = $unit->grade;

                $unitSequence = explode('.', $unit->code)[1] ?? '1';

                return "KIM.{$grade->number}.{$unitSequence}.{$topic->order}.".$this->faker->numberBetween(1, 9);
            },
            'description' => $this->faker->sentence(10),
            'bloom_level' => $this->faker->numberBetween(1, 6),
            'difficulty' => $this->faker->numberBetween(1, 5),
            'order' => $this->faker->numberBetween(0, 10),
            'is_active' => $this->faker->boolean(0.9),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set an explicit code, bypassing the chain-derived default.
     * Callers are responsible for keeping it unique within the owning Topic.
     */
    public function code(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }
}
