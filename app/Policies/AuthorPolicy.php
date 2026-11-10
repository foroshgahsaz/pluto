<?php

namespace App\Policies;

use App\Models\Author;
use App\Models\User;

class AuthorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('articles.view') || $user->isSuperAdmin();
    }

    public function view(User $user, Author $author): bool
    {
        return $this->viewAny($user) && $user->belongsToSite($author->site_id);
    }

    public function create(User $user): bool
    {
        return $user->can('articles.create') || $user->isSuperAdmin();
    }

    public function update(User $user, Author $author): bool
    {
        return ($user->can('articles.update') || $user->isSuperAdmin())
            && $user->belongsToSite($author->site_id);
    }

    public function delete(User $user, Author $author): bool
    {
        return ($user->can('articles.delete') || $user->isSuperAdmin())
            && $user->belongsToSite($author->site_id);
    }
}
