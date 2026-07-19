<?php

declare(strict_types=1);

namespace App\Enums;

enum ProgramStatus: int
{
    case Draft = 1;
    case Published = 2;
    case Archived = 3;

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
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
            self::Draft => 'gray',
            self::Published => 'success',
            self::Archived => 'warning',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Draft => 'Work in progress, not visible to students',
            self::Published => 'Live for students and teachers',
            self::Archived => 'Retired, preserved for historical reporting',
        };
    }

    public function canPublish(): bool
    {
        return $this === self::Draft;
    }

    public function canArchive(): bool
    {
        return in_array($this, [self::Draft, self::Published]);
    }

    public function canRestore(): bool
    {
        return $this === self::Archived;
    }

    public function canDelete(): bool
    {
        return true;
    }
}
