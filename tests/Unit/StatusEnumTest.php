<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ProgramStatus;
use App\Enums\TopicStatus;
use PHPUnit\Framework\TestCase;

/**
 * Regression coverage for DEBT-007: `array_column(self::cases(), 'label', 'value')`
 * silently returns an empty array, because `label` is a method on these enums,
 * not a property, and `array_column()` only reads properties. This broke
 * `ProgramStatus::options()` (confirmed live in the shipped Program status
 * filter) and, briefly, `TopicStatus::options()` during Sprint 1.4.
 *
 * Any future "Status"-style enum must satisfy this same contract: `options()`
 * returns one entry per case, keyed by `value`, with `label()` as the value.
 */
class StatusEnumTest extends TestCase
{
    public function test_program_status_options_returns_one_entry_per_case(): void
    {
        $options = ProgramStatus::options();

        $this->assertCount(count(ProgramStatus::cases()), $options);
        $this->assertNotEmpty($options);
    }

    public function test_program_status_options_keys_and_labels_are_correct(): void
    {
        $options = ProgramStatus::options();

        $this->assertSame([
            1 => 'Draft',
            2 => 'Published',
            3 => 'Archived',
        ], $options);
    }

    public function test_topic_status_options_returns_one_entry_per_case(): void
    {
        $options = TopicStatus::options();

        $this->assertCount(count(TopicStatus::cases()), $options);
        $this->assertNotEmpty($options);
    }

    public function test_topic_status_options_keys_and_labels_are_correct(): void
    {
        $options = TopicStatus::options();

        $this->assertSame([
            1 => 'Draft',
            2 => 'Published',
            3 => 'Archived',
        ], $options);
    }

    /**
     * Generic contract check: for every case, `options()` must contain that
     * case's value as a key with its label() as the value. This is the same
     * assertion as the two tests above, expressed generically so it keeps
     * working if either enum's cases/labels change.
     */
    public function test_program_status_options_match_each_case_label(): void
    {
        $options = ProgramStatus::options();

        foreach (ProgramStatus::cases() as $case) {
            $this->assertArrayHasKey($case->value, $options);
            $this->assertSame($case->label(), $options[$case->value]);
        }
    }

    public function test_topic_status_options_match_each_case_label(): void
    {
        $options = TopicStatus::options();

        foreach (TopicStatus::cases() as $case) {
            $this->assertArrayHasKey($case->value, $options);
            $this->assertSame($case->label(), $options[$case->value]);
        }
    }
}
