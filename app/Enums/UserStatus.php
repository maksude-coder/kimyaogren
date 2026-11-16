<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: int
{
    case Pending = 1;
    case Active = 2;
    case Suspended = 3;
    case Deleted = 4;

    /**
     * Whether an account in this status may sign in.
     */
    public function canSignIn(): bool
    {
        return in_array($this, [self::Pending, self::Active], true);
    }
}
