<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionStatus: int
{
    case Draft = 1;
    case Review = 2;
    case Approved = 3;
    case Deprecated = 4;

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'Review',
            self::Approved => 'Approved',
            self::Deprecated => 'Deprecated',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Review => 'warning',
            self::Approved => 'success',
            self::Deprecated => 'danger',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Draft => 'Author editing',
            self::Review => 'Submitted for review',
            self::Approved => 'Live for practice',
            self::Deprecated => 'Replaced, hidden',
        };
    }

    /**
     * Lifecycle: Draft → Review → Approved → Deprecated, and Review → Draft.
     */
    public function canTransitionTo(self $to): bool
    {
        return match ($this) {
            self::Draft => $to === self::Review,
            self::Review => in_array($to, [self::Draft, self::Approved], true),
            self::Approved => $to === self::Deprecated,
            self::Deprecated => false,
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
