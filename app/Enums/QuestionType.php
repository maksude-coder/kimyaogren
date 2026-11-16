<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
    case FillBlank = 'fill_blank';
    case Matching = 'matching';
    case Ordering = 'ordering';
    case Essay = 'essay';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Çoktan Seçmeli',
            self::TrueFalse => 'Doğru/Yanlış',
            self::ShortAnswer => 'Kısa Cevaplı',
            self::FillBlank => 'Boşluk Doldurma',
            self::Matching => 'Eşleştirme',
            self::Ordering => 'Sıralama',
            self::Essay => 'Açık Uçlu',
        };
    }

    /**
     * Whether the question bank can create this type yet (Sprint 3.1 plan § 2, rule 1).
     */
    public function isSupported(): bool
    {
        return in_array($this, [self::MultipleChoice, self::TrueFalse], true);
    }

    /**
     * Number of options a question of this type must have; null when not fixed.
     */
    public function optionCount(): ?int
    {
        return match ($this) {
            self::MultipleChoice => 5,
            self::TrueFalse => 2,
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }

    /**
     * Options for the types the question bank can create.
     *
     * @return array<string, string>
     */
    public static function supportedOptions(): array
    {
        return array_filter(
            self::options(),
            fn (string $value): bool => self::from($value)->isSupported(),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
