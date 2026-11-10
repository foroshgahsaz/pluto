<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->sites()->exists();
    }

    public function view(User $user, Menu $menu): bool
    {
        return $user->hasSitePermission('menus.manage', $menu->site_id);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('menus.manage', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->hasSitePermission('menus.manage', $menu->site_id);
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $user->hasSitePermission('menus.manage', $menu->site_id);
    }
}
