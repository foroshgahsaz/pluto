<?php

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGlobalOperator() || $user->sites()->exists();
    }

    public function view(User $user, SiteSetting $setting): bool
    {
        return $user->hasSitePermission('settings.manage', $setting->site_id);
    }

    public function create(User $user): bool
    {
        if ($user->isGlobalOperator()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('settings.manage', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, SiteSetting $setting): bool
    {
        return $user->hasSitePermission('settings.manage', $setting->site_id);
    }

    public function delete(User $user, SiteSetting $setting): bool
    {
        return $user->hasSitePermission('settings.manage', $setting->site_id);
    }
}
