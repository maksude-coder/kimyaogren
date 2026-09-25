<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Program;
use App\Models\User;
use App\Policies\Concerns\AuthorizesCurriculumManagement;

class ProgramPolicy
{
    use AuthorizesCurriculumManagement;

    public function viewAny(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function view(User $user, Program $program): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function update(User $user, Program $program): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function delete(User $user, Program $program): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function restore(User $user, Program $program): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function forceDelete(User $user, Program $program): bool
    {
        return $user->role === UserRole::Admin;
    }
}
