<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view') || $user->isGlobalOperator();
    }

    public function view(User $actor, User $model): bool
    {
        return $actor->can('users.view') || $actor->isGlobalOperator();
    }

    public function create(User $user): bool
    {
        return $user->can('users.create') || $user->isGlobalOperator();
    }

    public function update(User $actor, User $model): bool
    {
        // Only the developer may edit developer accounts.
        if ($model->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->can('users.update') || $actor->isGlobalOperator();
    }

    public function delete(User $actor, User $model): bool
    {
        if ($actor->id === $model->id) {
            return false;
        }

        if ($model->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->can('users.delete') || $actor->isGlobalOperator();
    }

    public function assignRoles(User $actor): bool
    {
        return $actor->can('users.assign_roles') || $actor->isGlobalOperator();
    }

    public function assignSuperAdmin(User $actor): bool
    {
        return $actor->isSuperAdmin();
    }

    public function assignPlatformAdmin(User $actor): bool
    {
        return $actor->isSuperAdmin();
    }

    public function promoteToSuperAdmin(User $actor, User $model): bool
    {
        return $actor->isSuperAdmin() && $actor->id !== $model->id;
    }
}
