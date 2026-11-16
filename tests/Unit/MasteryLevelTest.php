<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\MasteryLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MasteryLevelTest extends TestCase
{
    /**
     * @return array<string, array{float, int, MasteryLevel}>
     */
    public static function accuracies(): array
    {
        return [
            'four answers are not enough' => [100.0, 4, MasteryLevel::InsufficientData],
            'no answers' => [0.0, 0, MasteryLevel::InsufficientData],
            'five answers are enough' => [100.0, 5, MasteryLevel::Mastered],
            'just below 50' => [49.99, 20, MasteryLevel::Weak],
            'zero with enough answers' => [0.0, 5, MasteryLevel::Weak],
            'exactly 50' => [50.0, 20, MasteryLevel::Developing],
            'just below 65' => [64.99, 20, MasteryLevel::Developing],
            'exactly 65' => [65.0, 20, MasteryLevel::Proficient],
            'just below 80' => [79.99, 20, MasteryLevel::Proficient],
            'exactly 80' => [80.0, 20, MasteryLevel::Mastered],
        ];
    }

    #[DataProvider('accuracies')]
    public function test_level_follows_the_design_thresholds(float $percentage, int $attempts, MasteryLevel $expected): void
    {
        $this->assertSame($expected, MasteryLevel::fromAccuracy($percentage, $attempts));
    }

    public function test_values_match_the_data_dictionary(): void
    {
        $this->assertSame(
            [0 => 'InsufficientData', 1 => 'Weak', 2 => 'Developing', 3 => 'Proficient', 4 => 'Mastered'],
            array_column(MasteryLevel::cases(), 'name', 'value'),
        );
    }
}
