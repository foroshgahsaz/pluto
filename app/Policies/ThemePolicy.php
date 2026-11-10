<?php

namespace App\Policies;

use App\Models\Theme;
use App\Models\User;

class ThemePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('themes.manage', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function view(User $user, Theme $theme): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Theme $theme): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Theme $theme): bool
    {
        return $user->isSuperAdmin();
    }
}
