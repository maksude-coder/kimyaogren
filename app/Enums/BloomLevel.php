<?php

declare(strict_types=1);

namespace App\Enums;

enum BloomLevel: int
{
    case Remember = 1;
    case Understand = 2;
    case Apply = 3;
    case Analyze = 4;
    case Evaluate = 5;
    case Create = 6;

    public function label(): string
    {
        return match ($this) {
            self::Remember => 'Remember',
            self::Understand => 'Understand',
            self::Apply => 'Apply',
            self::Analyze => 'Analyze',
            self::Evaluate => 'Evaluate',
            self::Create => 'Create',
        };
    }

    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }

    public function color(): string
    {
        return match ($this) {
            self::Remember, self::Understand => 'gray',
            self::Apply, self::Analyze => 'info',
            self::Evaluate, self::Create => 'warning',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Remember => 'Recall facts, terms, basic concepts',
            self::Understand => 'Explain ideas, classify, summarize',
            self::Apply => 'Use info in new situations',
            self::Analyze => 'Draw connections, differentiate',
            self::Evaluate => 'Justify, critique, defend',
            self::Create => 'Produce new work, design',
        };
    }
}
