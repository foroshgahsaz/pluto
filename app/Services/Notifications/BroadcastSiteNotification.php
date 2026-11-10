<?php

namespace App\Services\Notifications;

use App\Models\Site;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class BroadcastSiteNotification
{
    public function __construct(
        protected SiteNotificationService $notifications,
    ) {}

    /**
     * @return int Number of recipients notified
     */
    public function handle(User $actor, int $siteId, string $title, string $body): int
    {
        if (! $actor->isSuperAdmin() && ! $actor->hasSitePermission('notifications.manage', $siteId)) {
            throw new AuthorizationException('مجوز مدیریت اعلان‌ها را ندارید.');
        }

        if (! $actor->isSuperAdmin() && ! $actor->belongsToSite($siteId)) {
            throw new AuthorizationException('به این سایت دسترسی ندارید.');
        }

        $site = Site::query()->findOrFail($siteId);
        $recipients = $this->recipientsForSite($siteId);

        foreach ($recipients as $recipient) {
            $recipient->notify(new \Filament\Notifications\DatabaseNotification(
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->info()
                    ->getDatabaseMessage() + [
                        'site_id' => $site->id,
                        'broadcast' => true,
                        'broadcast_by' => $actor->id,
                    ]
            ));
        }

        activity('notifications')
            ->causedBy($actor)
            ->withProperties([
                'site_id' => $siteId,
                'title' => $title,
                'recipients' => $recipients->count(),
            ])
            ->log('notification.broadcast');

        return $recipients->count();
    }

    /**
     * @return Collection<int, User>
     */
    public function recipientsForSite(int $siteId): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->where(function ($query) use ($siteId): void {
                $query->where('is_super_admin', true)
                    ->orWhereHas('sites', fn ($q) => $q->where('sites.id', $siteId));
            })
            ->get();
    }
}
