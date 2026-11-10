<?php

namespace App\Support\Tenancy;

use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AuthorizedSiteOptions
{
    /**
     * @return array<int|string, string>
     */
    public static function pluck(?User $user = null): array
    {
        $user ??= auth()->user();
        if (! $user) {
            return [];
        }

        $ids = $user->authorizedSiteIds() ?: [-1];

        return Site::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public static function assertWritable(User $user, int $siteId, string $permission): void
    {
        if ($user->isGlobalOperator()) {
            if (! Site::query()->whereKey($siteId)->exists()) {
                throw new AuthorizationException('سایت نامعتبر است.');
            }

            return;
        }

        if (! $user->hasSitePermission($permission, $siteId)) {
            throw new AuthorizationException('به این سایت برای این عملیات دسترسی ندارید.');
        }
    }
}
