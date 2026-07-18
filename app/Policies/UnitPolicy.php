<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function restore(User $user, Unit $unit): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function forceDelete(User $user, Unit $unit): bool
    {
        return $user->role === UserRole::Admin;
    }

    private function isAdminOrEditor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ContentEditor], true);
    }
}
