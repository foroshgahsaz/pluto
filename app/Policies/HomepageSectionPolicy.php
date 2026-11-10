<?php

namespace App\Policies;

use App\Models\HomepageSection;
use App\Models\User;

class HomepageSectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->sites()->exists();
    }

    public function view(User $user, HomepageSection $section): bool
    {
        return $user->hasSitePermission('settings.manage', $section->site_id);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('settings.manage', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, HomepageSection $section): bool
    {
        return $user->hasSitePermission('settings.manage', $section->site_id);
    }

    public function delete(User $user, HomepageSection $section): bool
    {
        return $user->hasSitePermission('settings.manage', $section->site_id);
    }
}
