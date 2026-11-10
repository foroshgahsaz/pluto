<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sites.view') || $user->isSuperAdmin();
    }

    public function view(User $user, Site $site): bool
    {
        return ($user->can('sites.view') || $user->isSuperAdmin())
            && $user->belongsToSite($site->id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('sites.create');
    }

    public function update(User $user, Site $site): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->can('sites.update') && $user->belongsToSite($site->id);
    }

    public function delete(User $user, Site $site): bool
    {
        return $user->isSuperAdmin() || ($user->can('sites.delete') && $user->belongsToSite($site->id));
    }
}
