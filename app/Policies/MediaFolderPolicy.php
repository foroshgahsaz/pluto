<?php

namespace App\Policies;

use App\Models\MediaFolder;
use App\Models\User;

class MediaFolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGlobalOperator() || $user->sites()->exists();
    }

    public function view(User $user, MediaFolder $folder): bool
    {
        return $user->hasSitePermission('media.view', $folder->site_id);
    }

    public function create(User $user): bool
    {
        return $user->can('create', \App\Models\Media::class);
    }

    public function update(User $user, MediaFolder $folder): bool
    {
        return $user->hasSitePermission('media.update', $folder->site_id);
    }

    public function delete(User $user, MediaFolder $folder): bool
    {
        return $user->hasSitePermission('media.delete', $folder->site_id);
    }
}
