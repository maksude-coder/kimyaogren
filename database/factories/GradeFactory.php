<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    /**
     * Define the model's default state.
     *
     * Business rule (dd-grades.md): `number` must fall within the owning
     * Program's min_grade-max_grade range. `program_id` is resolved first
     * (Laravel factories resolve nested factory relationships before
     * evaluating sibling closures), so `number` reads the real, persisted
     * Program's range rather than guessing an independent value — the same
     * class of bug fixed in ProgramFactory (min_grade/max_grade no longer
     * chosen independently) must not be reintroduced here one level down.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'number' => function (array $attributes) {
                $program = Program::find($attributes['program_id']);

                return $this->faker->numberBetween($program->min_grade, $program->max_grade);
            },
            'name' => function (array $attributes) {
                return $attributes['number'].'. Sınıf';
            },
            'description' => $this->faker->optional(0.7)->sentence(10),
            'order' => $this->faker->numberBetween(0, 10),
            'is_active' => $this->faker->boolean(0.9),
            'meta_title' => $this->faker->optional(0.5)->words(6, true),
            'meta_description' => $this->faker->optional(0.5)->sentence(12),
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
     * Set an explicit grade number, bypassing the Program-range default.
     * Callers are responsible for keeping it within the owning Program's
     * min_grade-max_grade range and unique per Program.
     */
    public function number(int $number): static
    {
        return $this->state(fn (array $attributes) => [
            'number' => $number,
            'name' => $number.'. Sınıf',
        ]);
    }
}
