<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // DEBT-009: `unique()` was previously applied to `randomElement()`
        // over this same fixed 15-item list, which became flaky once the
        // cumulative test suite (Grade/Unit/Topic/LearningObjective all
        // indirectly create Programs via nested factory defaults) needed
        // more than 15 distinct Programs per process. `unique()` now
        // applies instead to a 9,000-value numeric differentiator
        // (1000-9999), giving 15 x 9,000 = 135,000 possible names — the
        // base list itself no longer needs to be unique. The 4-digit range
        // is deliberate: the longest base name ("Cambridge IGCSE
        // Chemistry") produces a 25-character `code`, and `code` is
        // VARCHAR(30) — 25 + "_" + 4 digits = 30 exactly, so a 5th digit
        // would silently risk truncation/overflow for that name.
        $name = $this->faker->randomElement([
            'High School Chemistry',
            'High School Physics',
            'Middle School Science',
            'IB Chemistry SL',
            'IB Chemistry HL',
            'AP Chemistry',
            'AP Physics 1',
            'AP Physics 2',
            'Cambridge IGCSE Chemistry',
            'Cambridge IGCSE Physics',
            'A-Level Chemistry',
            'A-Level Physics',
            'Middle School Chemistry',
            'High School Biology',
            'IB Biology SL',
        ]).' '.$this->faker->unique()->numberBetween(1000, 9999);

        $code = Str::upper(Str::slug($name, '_'));
        $slug = Str::slug($name);
        $minGrade = $this->faker->numberBetween(5, 11);
        $maxGrade = $this->faker->numberBetween($minGrade, 12);

        return [
            'uuid' => $this->faker->uuid(),
            'code' => $code,
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->optional(0.8)->paragraph(3),
            'short_description' => $this->faker->optional(0.7)->sentence(10),
            'primary_language' => $this->faker->randomElement(['tr', 'en']),
            'supported_languages' => $this->faker->randomElement([['tr'], ['en'], ['tr', 'en']]),
            'min_grade' => $minGrade,
            'max_grade' => $maxGrade,
            'status' => $this->faker->randomElement([ProgramStatus::Draft->value, ProgramStatus::Published->value, ProgramStatus::Archived->value]),
            'is_active' => $this->faker->boolean(0.9),
            'display_order' => $this->faker->numberBetween(0, 100),
            'version' => $this->faker->numberBetween(1, 5),
            'meta_title' => $this->faker->optional(0.7)->words(6, true),
            'meta_description' => $this->faker->optional(0.7)->sentence(12),
            'meta_keywords' => $this->faker->optional(0.5)->words(8, true),
            'settings' => [
                'pacing' => [
                    'weeks_per_unit' => fake()->numberBetween(2, 6),
                    'allow_self_paced' => true,
                    'enforce_sequential' => false,
                ],
                'assessment' => [
                    'passing_score' => 70,
                    'allow_retakes' => true,
                    'max_retakes' => 3,
                ],
                'features' => [
                    'spaced_repetition' => true,
                    'gamification' => false,
                    'parent_portal' => false,
                    'offline_mode' => true,
                ],
                'grading' => [
                    'scale' => 'percentage',
                    'show_ranking' => false,
                    'weight' => [
                        'practice' => 40,
                        'quiz' => 30,
                        'exam' => 30,
                    ],
                ],
                'notifications' => [
                    'due_reminders' => true,
                    'weekly_summary' => true,
                    'achievement_alerts' => false,
                ],
            ],
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProgramStatus::Published->value,
            'is_active' => true,
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProgramStatus::Draft->value,
            'published_at' => null,
            'archived_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProgramStatus::Archived->value,
            'is_active' => false,
            'archived_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function turkish(): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_language' => 'tr',
            'supported_languages' => ['tr'],
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_language' => 'en',
            'supported_languages' => ['en'],
        ]);
    }

    public function bilingual(): static
    {
        return $this->state(fn (array $attributes) => [
            'supported_languages' => ['tr', 'en'],
        ]);
    }

    public function highSchool(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_grade' => 9,
            'max_grade' => 12,
        ]);
    }

    public function middleSchool(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_grade' => 5,
            'max_grade' => 8,
        ]);
    }
}
