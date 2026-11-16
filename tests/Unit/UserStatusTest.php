<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\UserStatus;
use PHPUnit\Framework\TestCase;

class UserStatusTest extends TestCase
{
    public function test_values_match_the_data_dictionary(): void
    {
        $this->assertSame(
            ['Pending' => 1, 'Active' => 2, 'Suspended' => 3, 'Deleted' => 4],
            array_column(UserStatus::cases(), 'value', 'name'),
        );
    }

    public function test_only_pending_and_active_accounts_may_sign_in(): void
    {
        $this->assertTrue(UserStatus::Pending->canSignIn());
        $this->assertTrue(UserStatus::Active->canSignIn());
        $this->assertFalse(UserStatus::Suspended->canSignIn());
        $this->assertFalse(UserStatus::Deleted->canSignIn());
    }
}
