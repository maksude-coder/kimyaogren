<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    /**
     * Define the model's default state.
     *
     * Business rule (dd-units.md): `code` follows `{grade_number}.{unit_sequence}`
     * and is unique within the owning Grade, not globally. `grade_id` is
     * resolved first (nested factory relationships resolve before sibling
     * closures), so `code` reads the real, persisted Grade's `number` rather
     * than guessing independently — the same class of fix already applied
     * in `GradeFactory` (`number` scoped to the owning Program), one level
     * down. Faker's global `unique()` is deliberately not used for `code`,
     * since the same code (e.g. "9.1") legitimately exists for different
     * Grades — only `->for($grade)->code(...)` guarantees no collision
     * within a single Grade, mirroring `GradeFactory::number()`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grade_id' => Grade::factory(),
            'code' => function (array $attributes) {
                $grade = Grade::find($attributes['grade_id']);

                return $grade->number.'.'.$this->faker->numberBetween(1, 9);
            },
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->optional(0.7)->sentence(10),
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
     * Set an explicit unit code, bypassing the Grade-derived default.
     * Callers are responsible for keeping it unique within the owning Grade.
     */
    public function code(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }
}
