<?php

namespace App\Services\Dashboard;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use App\Support\Tenancy\SiteScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class EditorialDashboardService
{
    /**
     * @return list<int|string>
     */
    public function authorizedSiteIds(User $user): array
    {
        return $user->authorizedSiteIds();
    }

    /**
     * @param  list<int|string>|null  $siteIds
     * @return array{total: int, published: int, draft: int, in_review: int, scheduled: int}
     */
    public function articleCounts(User $user, ?array $siteIds = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $siteIds ??= $this->authorizedSiteIds($user);
        $query = Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds ?: [-1]);

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $rows = $query
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $byStatus = fn (ArticleStatus $status): int => (int) ($rows[$status->value] ?? 0);

        return [
            'total' => (int) $rows->sum(),
            'published' => $byStatus(ArticleStatus::Published),
            'draft' => $byStatus(ArticleStatus::Draft),
            'in_review' => $byStatus(ArticleStatus::InReview),
            'scheduled' => $byStatus(ArticleStatus::Scheduled),
        ];
    }

    /**
     * @param  list<int|string>|null  $siteIds
     * @return array{labels: list<string>, data: list<int>}
     */
    public function publishVolumeByDay(User $user, int $days = 14, ?array $siteIds = null): array
    {
        $siteIds ??= $this->authorizedSiteIds($user);
        $start = now()->subDays($days - 1)->startOfDay();

        $counts = Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds ?: [-1])
            ->where('status', ArticleStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '>=', $start)
            ->selectRaw('DATE(published_at) as day, COUNT(*) as aggregate')
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        $labels = [];
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('m-d');
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @param  list<int|string>|null  $siteIds
     * @return Collection<int, Activity>
     */
    public function recentEditorialActivity(User $user, int $limit = 15, ?array $siteIds = null): Collection
    {
        $siteIds ??= $this->authorizedSiteIds($user);
        $siteIds = array_map('intval', $siteIds ?: [-1]);

        return Activity::query()
            ->where('log_name', 'editorial')
            ->where(function ($query) use ($siteIds): void {
                foreach ($siteIds as $siteId) {
                    $query->orWhere('properties->site_id', $siteId);
                }
            })
            ->latest()
            ->limit($limit)
            ->get();
    }
}
