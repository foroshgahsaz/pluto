<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGlobalOperator() || $user->sites()->exists();
    }

    public function view(User $user, Media $media): bool
    {
        return $user->hasSitePermission('media.view', $media->site_id);
    }

    public function create(User $user): bool
    {
        if ($user->isGlobalOperator()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('media.upload', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, Media $media): bool
    {
        return $user->hasSitePermission('media.update', $media->site_id);
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->hasSitePermission('media.delete', $media->site_id);
    }

    public function downloadPrivate(User $user, Media $media): bool
    {
        return $user->hasSitePermission('media.download_private', $media->site_id)
            || $user->hasSitePermission('media.view', $media->site_id);
    }
}
