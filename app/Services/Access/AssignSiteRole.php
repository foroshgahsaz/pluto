<?php

namespace App\Services\Access;

use App\Models\Site;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\Notifications\SiteNotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class AssignSiteRole
{
    public function handle(User $actor, User $target, int $siteId, string $roleName): void
    {
        if ($roleName === 'super_admin' || $target->is_super_admin && $roleName === 'super_admin') {
            throw new AuthorizationException('نقش سوپر ادمین از این مسیر قابل انتساب نیست.');
        }

        if (! app(UserPolicy::class)->assignRoles($actor) && ! $actor->isSuperAdmin()) {
            throw new AuthorizationException('مجوز انتساب نقش ندارید.');
        }

        if (! $actor->isSuperAdmin() && ! $actor->belongsToSite($siteId)) {
            throw new AuthorizationException('به این سایت دسترسی ندارید.');
        }

        DB::transaction(function () use ($target, $siteId, $roleName): void {
            $target->sites()->syncWithoutDetaching([$siteId]);

            setPermissionsTeamId($siteId);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $target->syncRoles([$roleName]);
        });

        $site = Site::query()->find($siteId);
        if ($site) {
            app(SiteNotificationService::class)->notifyRoleAssigned($target->fresh(), $site, $roleName);
        }
    }

    public function promoteSuperAdmin(User $actor, User $target): void
    {
        if (! app(UserPolicy::class)->promoteToSuperAdmin($actor, $target)) {
            throw new AuthorizationException('فقط سوپر ادمین می‌تواند سوپر ادمین جدید بسازد.');
        }

        $target->forceFill(['is_super_admin' => true])->save();
    }
}
