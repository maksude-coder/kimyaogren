<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Enums\YksSession;
use PHPUnit\Framework\TestCase;

class QuestionEnumTest extends TestCase
{
    public function test_question_type_values_match_the_data_dictionary(): void
    {
        $this->assertSame(
            ['multiple_choice', 'true_false', 'short_answer', 'fill_blank', 'matching', 'ordering', 'essay'],
            array_column(QuestionType::cases(), 'value'),
        );
    }

    public function test_only_multiple_choice_and_true_false_are_supported(): void
    {
        $this->assertSame(
            ['multiple_choice' => 'Çoktan Seçmeli', 'true_false' => 'Doğru/Yanlış'],
            QuestionType::supportedOptions(),
        );
        $this->assertCount(7, QuestionType::options());
    }

    public function test_option_counts(): void
    {
        $this->assertSame(5, QuestionType::MultipleChoice->optionCount());
        $this->assertSame(2, QuestionType::TrueFalse->optionCount());
        $this->assertNull(QuestionType::Essay->optionCount());
    }

    public function test_status_options_cover_every_case(): void
    {
        $this->assertSame(
            [1 => 'Draft', 2 => 'Review', 3 => 'Approved', 4 => 'Deprecated'],
            QuestionStatus::options(),
        );

        foreach (QuestionStatus::cases() as $status) {
            $this->assertNotSame('', $status->color());
            $this->assertNotSame('', $status->description());
        }
    }

    public function test_status_transitions_follow_the_lifecycle(): void
    {
        $allowed = [
            [QuestionStatus::Draft, QuestionStatus::Review],
            [QuestionStatus::Review, QuestionStatus::Draft],
            [QuestionStatus::Review, QuestionStatus::Approved],
            [QuestionStatus::Approved, QuestionStatus::Deprecated],
        ];

        foreach (QuestionStatus::cases() as $from) {
            foreach (QuestionStatus::cases() as $to) {
                $this->assertSame(
                    in_array([$from, $to], $allowed, true),
                    $from->canTransitionTo($to),
                    "{$from->name} → {$to->name}",
                );
            }
        }
    }

    public function test_difficulty_and_session_options(): void
    {
        $this->assertSame([1, 2, 3, 4, 5], array_keys(QuestionDifficulty::options()));
        $this->assertSame('3 — Medium', QuestionDifficulty::options()[3]);
        $this->assertSame([1 => 'TYT', 2 => 'AYT'], YksSession::options());
    }
}
