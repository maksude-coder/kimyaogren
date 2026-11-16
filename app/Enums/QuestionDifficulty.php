<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionDifficulty: int
{
    case VeryEasy = 1;
    case Easy = 2;
    case Medium = 3;
    case Hard = 4;
    case VeryHard = 5;

    public function label(): string
    {
        return match ($this) {
            self::VeryEasy => 'Very Easy',
            self::Easy => 'Easy',
            self::Medium => 'Medium',
            self::Hard => 'Hard',
            self::VeryHard => 'Very Hard',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->value.' — '.$case->label(), self::cases()),
        );
    }
}
