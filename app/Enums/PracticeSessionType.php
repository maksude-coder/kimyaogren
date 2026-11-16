<?php

declare(strict_types=1);

namespace App\Enums;

enum PracticeSessionType: string
{
    case Topic = 'topic';
    case MixedReview = 'mixed_review';
    case WeakAreas = 'weak_areas';
    case TimedQuiz = 'timed_quiz';
    case ExamSimulation = 'exam_simulation';

    public function label(): string
    {
        return match ($this) {
            self::Topic => 'Konu Çalışması',
            self::MixedReview => 'Karma Tekrar',
            self::WeakAreas => 'Zayıf Alanlar',
            self::TimedQuiz => 'Zamanlı Quiz',
            self::ExamSimulation => 'Sınav Simülasyonu',
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
}
