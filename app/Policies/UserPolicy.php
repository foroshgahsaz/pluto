<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view') || $user->isSuperAdmin();
    }

    public function view(User $actor, User $model): bool
    {
        return $actor->can('users.view') || $actor->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->can('users.create') || $user->isSuperAdmin();
    }

    public function update(User $actor, User $model): bool
    {
        return $actor->can('users.update') || $actor->isSuperAdmin();
    }

    public function delete(User $actor, User $model): bool
    {
        if ($actor->id === $model->id) {
            return false;
        }

        return $actor->can('users.delete') || $actor->isSuperAdmin();
    }

    public function assignRoles(User $actor): bool
    {
        return $actor->can('users.assign_roles') || $actor->isSuperAdmin();
    }

    public function assignSuperAdmin(User $actor): bool
    {
        return $actor->isSuperAdmin();
    }

    public function promoteToSuperAdmin(User $actor, User $model): bool
    {
        return $actor->isSuperAdmin() && $actor->id !== $model->id;
    }
}

