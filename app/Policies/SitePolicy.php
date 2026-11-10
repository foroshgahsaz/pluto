<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sites.view') || $user->isGlobalOperator();
    }

    public function view(User $user, Site $site): bool
    {
        return ($user->can('sites.view') || $user->isGlobalOperator())
            && $user->belongsToSite($site->id);
    }

    public function create(User $user): bool
    {
        return $user->isGlobalOperator() || $user->can('sites.create');
    }

    public function update(User $user, Site $site): bool
    {
        if ($user->isGlobalOperator()) {
            return true;
        }

        return $user->can('sites.update') && $user->belongsToSite($site->id);
    }

    public function delete(User $user, Site $site): bool
    {
        return $user->isGlobalOperator()
            || ($user->can('sites.delete') && $user->belongsToSite($site->id));
    }
}
