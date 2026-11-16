<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * A student's level in a topic (design § 06, Sprint 3.3 plan § 2, rules 2–3).
 */
enum MasteryLevel: int
{
    case InsufficientData = 0;
    case Weak = 1;
    case Developing = 2;
    case Proficient = 3;
    case Mastered = 4;

    /** Answers needed before a level other than InsufficientData is given. */
    public const MINIMUM_ATTEMPTS = 5;

    /**
     * Level for an accuracy (0–100) over the given number of answers.
     */
    public static function fromAccuracy(float $percentage, int $attempts): self
    {
        return match (true) {
            $attempts < self::MINIMUM_ATTEMPTS => self::InsufficientData,
            $percentage >= 80 => self::Mastered,
            $percentage >= 65 => self::Proficient,
            $percentage >= 50 => self::Developing,
            default => self::Weak,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::InsufficientData => 'Insufficient data',
            self::Weak => 'Weak',
            self::Developing => 'Developing',
            self::Proficient => 'Proficient',
            self::Mastered => 'Mastered',
        };
    }
}
