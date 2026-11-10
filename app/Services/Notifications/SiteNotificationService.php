<?php

namespace App\Services\Notifications;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Site;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SiteNotificationService
{
    public function notifyArticleTransition(User $actor, Article $article, ArticleStatus $from, ArticleStatus $to): void
    {
        $siteName = $article->site?->name ?? ('سایت #'.$article->site_id);

        match ($to) {
            ArticleStatus::InReview => $this->notifyReviewers($article, $actor, $siteName),
            ArticleStatus::Approved => $this->notifyCreator(
                $article,
                'خبر تأیید شد',
                "خبر «{$article->title}» در {$siteName} تأیید شد.",
            ),
            ArticleStatus::Rejected => $this->notifyCreator(
                $article,
                'خبر رد شد',
                "خبر «{$article->title}» در {$siteName} رد شد.".($article->rejection_reason ? " دلیل: {$article->rejection_reason}" : ''),
            ),
            ArticleStatus::Published => $this->notifyCreator(
                $article,
                'خبر منتشر شد',
                "خبر «{$article->title}» در {$siteName} منتشر شد.",
            ),
            default => null,
        };
    }

    public function notifyRoleAssigned(User $target, Site $site, string $roleName): void
    {
        $this->send($target, Notification::make()
            ->title('نقش سایت به‌روزرسانی شد')
            ->body("نقش «{$roleName}» برای سایت «{$site->name}» برای شما تنظیم شد.")
            ->success()
            ->getDatabaseMessage() + ['site_id' => $site->id]);
    }

    public function maybeNotifyFailedLoginThreshold(?string $mobile, ?string $ip): void
    {
        $threshold = (int) config('pluto.security.failed_login_threshold', 5);
        $window = (int) config('pluto.security.failed_login_window_minutes', 15);

        if ($threshold < 1) {
            return;
        }

        $query = \App\Models\LoginEvent::query()
            ->where('event', 'login_failed')
            ->where('created_at', '>=', now()->subMinutes($window));

        if ($mobile) {
            $query->where('mobile', $mobile);
        } elseif ($ip) {
            $query->where('ip', $ip);
        } else {
            return;
        }

        $count = $query->count();
        if ($count < $threshold) {
            return;
        }

        $dedupeKey = 'failed_login_alert:'.md5(($mobile ?? '').'|'.($ip ?? '').'|'.now()->format('YmdHi'));
        if (! Cache::add($dedupeKey, true, now()->addMinutes($window))) {
            return;
        }

        $body = sprintf(
            'در %d دقیقه اخیر %d ورود ناموفق%s%s ثبت شد.',
            $window,
            $count,
            $mobile ? " برای موبایل {$mobile}" : '',
            $ip ? " از IP {$ip}" : '',
        );

        foreach (User::query()->where('is_super_admin', true)->where('is_active', true)->get() as $admin) {
            $this->send($admin, Notification::make()
                ->title('هشدار امنیتی: ورودهای ناموفق')
                ->body($body)
                ->danger()
                ->getDatabaseMessage());
        }
    }

    protected function notifyReviewers(Article $article, User $actor, string $siteName): void
    {
        $recipients = $this->usersWithSitePermission((int) $article->site_id, 'articles.approve')
            ->reject(fn (User $user) => $actor->exists && $user->is($actor));

        foreach ($recipients as $recipient) {
            $this->send($recipient, Notification::make()
                ->title('خبر جدید برای بررسی')
                ->body("خبر «{$article->title}» در {$siteName} برای بررسی ارسال شد.")
                ->info()
                ->getDatabaseMessage() + [
                    'site_id' => $article->site_id,
                    'article_id' => $article->id,
                ]);
        }
    }

    protected function notifyCreator(Article $article, string $title, string $body): void
    {
        $creator = $article->creator;
        if (! $creator) {
            return;
        }

        $this->send($creator, Notification::make()
            ->title($title)
            ->body($body)
            ->success()
            ->getDatabaseMessage() + [
                'site_id' => $article->site_id,
                'article_id' => $article->id,
            ]);
    }

    /**
     * @return Collection<int, User>
     */
    public function usersWithSitePermission(int $siteId, string $permission): Collection
    {
        $users = User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($siteId): void {
                $query->where('is_super_admin', true)
                    ->orWhereHas('sites', fn ($q) => $q->where('sites.id', $siteId));
            })
            ->get();

        return $users->filter(fn (User $user) => $user->hasSitePermission($permission, $siteId))->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function send(User $user, array $data): void
    {
        $user->notify(new \Filament\Notifications\DatabaseNotification($data));
    }
}
