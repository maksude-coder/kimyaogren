<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function view(User $user, Grade $grade): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function update(User $user, Grade $grade): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function restore(User $user, Grade $grade): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function forceDelete(User $user, Grade $grade): bool
    {
        return $user->role === UserRole::Admin;
    }

    private function isAdminOrEditor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ContentEditor], true);
    }
}
