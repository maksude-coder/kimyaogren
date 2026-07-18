<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TopicStatus;
use App\Models\Topic;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Topic>
 */
class TopicFactory extends Factory
{
    protected $model = Topic::class;

    /**
     * Define the model's default state.
     *
     * Business rule (dd-topics.md): `slug` is unique within the owning Unit,
     * not globally — the same slug text could legitimately repeat across
     * different Units. Faker's global `unique()` is deliberately not used
     * for `name`/`slug` here; only `->for($unit)->slug(...)` guarantees no
     * collision within a single Unit, mirroring `UnitFactory::code()` and
     * `GradeFactory::number()`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'unit_id' => Unit::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->optional(0.7)->paragraph(2),
            'order' => $this->faker->numberBetween(0, 10),
            'difficulty' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement([TopicStatus::Draft->value, TopicStatus::Published->value, TopicStatus::Archived->value]),
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

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TopicStatus::Published->value,
            'is_active' => true,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TopicStatus::Draft->value,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TopicStatus::Archived->value,
            'is_active' => false,
        ]);
    }

    /**
     * Set an explicit name (and derived slug), bypassing the random default.
     * Callers are responsible for keeping the slug unique within the owning Unit.
     */
    public function named(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }

    /**
     * Set an explicit slug, bypassing the name-derived default.
     * Callers are responsible for keeping it unique within the owning Unit.
     */
    public function slug(string $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => $slug,
        ]);
    }
}
