<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Shared authorization rule for the curriculum domain policies
 * (Program, Grade, Unit, Topic, LearningObjective): admins and
 * content editors may manage records, everyone else may not.
 */
trait AuthorizesCurriculumManagement
{
    private function isAdminOrEditor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ContentEditor], true);
    }
}
