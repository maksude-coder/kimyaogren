<?php

declare(strict_types=1);

namespace App\Enums;

enum YksSession: int
{
    case Tyt = 1;
    case Ayt = 2;

    public function label(): string
    {
        return match ($this) {
            self::Tyt => 'TYT',
            self::Ayt => 'AYT',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
