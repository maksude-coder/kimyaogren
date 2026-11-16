<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function view(User $user, Question $question): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function update(User $user, Question $question): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function restore(User $user, Question $question): bool
    {
        return $this->isAdminOrEditor($user);
    }

    public function forceDelete(User $user, Question $question): bool
    {
        return $user->role === UserRole::Admin;
    }

    private function isAdminOrEditor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ContentEditor], true);
    }
}
