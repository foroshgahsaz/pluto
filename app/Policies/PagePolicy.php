<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGlobalOperator() || $user->sites()->exists();
    }

    public function view(User $user, Page $page): bool
    {
        return $user->hasSitePermission('pages.manage', $page->site_id);
    }

    public function create(User $user): bool
    {
        if ($user->isGlobalOperator()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('pages.manage', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, Page $page): bool
    {
        return $user->hasSitePermission('pages.manage', $page->site_id);
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->hasSitePermission('pages.manage', $page->site_id);
    }
}
