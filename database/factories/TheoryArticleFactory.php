<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\TheoryArticle;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TheoryArticle>
 */
class TheoryArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title($this->faker->unique()->words(4, true));

        return [
            'topic_id' => Topic::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->optional(0.7)->sentence(15),
            'order' => $this->faker->numberBetween(0, 10),
            'difficulty' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(ArticleStatus::cases())->value,
            'is_active' => $this->faker->boolean(0.9),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ArticleStatus::Published->value,
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ArticleStatus::Draft->value,
        ]);
    }

    public function legacy(string $slug, ?int $wpId = null): static
    {
        return $this->state(fn () => [
            'legacy_slug' => $slug,
            'legacy_wp_id' => $wpId ?? $this->faker->unique()->numberBetween(1, 1_000_000),
        ]);
    }

    /**
     * Attach a published version 1 with the given HTML.
     */
    public function withContent(string $html = '<p>İçerik</p>'): static
    {
        return $this->afterCreating(function (TheoryArticle $article) use ($html) {
            $article->versions()->create([
                'version_number' => 1,
                'title' => $article->title,
                'content' => $html,
                'word_count' => str_word_count(strip_tags($html)),
                'is_published_version' => true,
            ]);
        });
    }
}
