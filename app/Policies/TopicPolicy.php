<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Topic;
use App\Models\User;

class TopicPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function view(User $user, Topic $topic): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function update(User $user, Topic $topic): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function delete(User $user, Topic $topic): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function restore(User $user, Topic $topic): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function forceDelete(User $user, Topic $topic): bool
    {
        return $user->role === UserRole::Admin;
    }

    private function isAdminOrEditor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ContentEditor], true);
    }
}
