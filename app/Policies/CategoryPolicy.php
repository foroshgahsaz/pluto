<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('categories.view') || $user->isSuperAdmin();
    }

    public function view(User $user, Category $category): bool
    {
        return ($user->can('categories.view') || $user->isSuperAdmin())
            && $user->belongsToSite($category->site_id);
    }

    public function create(User $user): bool
    {
        return $user->can('categories.create') || $user->isSuperAdmin();
    }

    public function update(User $user, Category $category): bool
    {
        return ($user->can('categories.update') || $user->isSuperAdmin())
            && $user->belongsToSite($category->site_id);
    }

    public function delete(User $user, Category $category): bool
    {
        return ($user->can('categories.delete') || $user->isSuperAdmin())
            && $user->belongsToSite($category->site_id);
    }
}
