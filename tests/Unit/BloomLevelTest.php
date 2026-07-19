<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\BloomLevel;
use PHPUnit\Framework\TestCase;

/**
 * Written proactively (Sprint 1.5, lesson learned #8 from DEBT-007): every
 * enum's `options()` must be regression-tested from the start using the
 * `array_combine()`/`array_map()` pattern, not discovered broken after the
 * fact as happened with `TopicStatus`/`ProgramStatus`.
 */
class BloomLevelTest extends TestCase
{
    public function test_bloom_level_options_returns_one_entry_per_case(): void
    {
        $options = BloomLevel::options();

        $this->assertCount(count(BloomLevel::cases()), $options);
        $this->assertNotEmpty($options);
    }

    public function test_bloom_level_options_keys_and_labels_are_correct(): void
    {
        $options = BloomLevel::options();

        $this->assertSame([
            1 => 'Remember',
            2 => 'Understand',
            3 => 'Apply',
            4 => 'Analyze',
            5 => 'Evaluate',
            6 => 'Create',
        ], $options);
    }

    public function test_bloom_level_options_match_each_case_label(): void
    {
        $options = BloomLevel::options();

        foreach (BloomLevel::cases() as $case) {
            $this->assertArrayHasKey($case->value, $options);
            $this->assertSame($case->label(), $options[$case->value]);
        }
    }
}
