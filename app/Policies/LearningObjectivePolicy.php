<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LearningObjective;
use App\Models\User;
use App\Policies\Concerns\AuthorizesCurriculumManagement;

class LearningObjectivePolicy
{
    use AuthorizesCurriculumManagement;

    public function viewAny(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function view(User $user, LearningObjective $learningObjective): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function update(User $user, LearningObjective $learningObjective): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function delete(User $user, LearningObjective $learningObjective): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function restore(User $user, LearningObjective $learningObjective): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function forceDelete(User $user, LearningObjective $learningObjective): bool
    {
        return $user->role === UserRole::Admin;
    }
}
