<?php

declare(strict_types=1);

namespace App\Enums;

enum ArticleStatus: int
{
    case Draft = 1;
    case Review = 2;
    case Published = 3;
    case Archived = 4;

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'Review',
            self::Published => 'Published',
            self::Archived => 'Archived',
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

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Review => 'info',
            self::Published => 'success',
            self::Archived => 'warning',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Draft => 'Author editing',
            self::Review => 'Submitted for editorial review',
            self::Published => 'Live for students',
            self::Archived => 'Retired, preserved',
        };
    }
}
