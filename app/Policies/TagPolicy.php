<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('categories.view') || $user->can('articles.view') || $user->isSuperAdmin();
    }

    public function view(User $user, Tag $tag): bool
    {
        return $this->viewAny($user) && $user->belongsToSite($tag->site_id);
    }

    public function create(User $user): bool
    {
        return $user->can('articles.create') || $user->isSuperAdmin();
    }

    public function update(User $user, Tag $tag): bool
    {
        return ($user->can('articles.update') || $user->isSuperAdmin())
            && $user->belongsToSite($tag->site_id);
    }

    public function delete(User $user, Tag $tag): bool
    {
        return ($user->can('articles.delete') || $user->isSuperAdmin())
            && $user->belongsToSite($tag->site_id);
    }
}
