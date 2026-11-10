<?php

namespace App\Services\Dashboard;

use App\Enums\ArticleStatus;
use App\Enums\SiteType;
use App\Models\Article;
use App\Models\Media;
use App\Models\Site;
use App\Models\SiteDailyMetric;
use App\Models\User;
use App\Support\PersianDate;
use App\Support\Tenancy\SiteScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class AdminDashboardService
{
    /**
     * @return list<int>
     */
    public function siteIds(User $user): array
    {
        return array_map('intval', $user->authorizedSiteIds() ?: [-1]);
    }

    /**
     * @return array{
     *   active_sites:int, sites_delta:int,
     *   users:int, users_delta_pct:float,
     *   published:int, published_delta_pct:float,
     *   views:int, views_delta_pct:float
     * }
     */
    public function headlineStats(User $user): array
    {
        $siteIds = $this->siteIds($user);
        $monthStart = now()->startOfMonth();
        $prevMonthStart = now()->subMonth()->startOfMonth();
        $prevMonthEnd = now()->subMonth()->endOfMonth();

        $activeSites = Site::query()->whereIn('id', $siteIds)->where('is_active', true)->count();
        $sitesDelta = Site::query()
            ->whereIn('id', $siteIds)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $usersQuery = User::query()->where('is_active', true)->whereNull('deleted_at');
        if (! $user->isGlobalOperator()) {
            $usersQuery->whereHas('sites', fn ($q) => $q->whereIn('sites.id', $siteIds));
        }
        $users = (clone $usersQuery)->count();
        $usersPrev = (clone $usersQuery)->where('created_at', '<=', $prevMonthEnd)->count();

        $published = Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds)
            ->where('status', ArticleStatus::Published)
            ->count();
        $publishedThisMonth = Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds)
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '>=', $monthStart)
            ->count();
        $publishedPrevMonth = Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds)
            ->where('status', ArticleStatus::Published)
            ->whereBetween('published_at', [$prevMonthStart, $prevMonthEnd])
            ->count();

        $articleViews = (int) Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds)
            ->sum('view_count');
        $metricViews = (int) SiteDailyMetric::query()
            ->whereIn('site_id', $siteIds)
            ->sum('page_views');
        $views = max($articleViews, $metricViews);

        $viewsThisMonth = (int) SiteDailyMetric::query()
            ->whereIn('site_id', $siteIds)
            ->where('metric_date', '>=', $monthStart->toDateString())
            ->sum('page_views');
        $viewsPrevMonth = (int) SiteDailyMetric::query()
            ->whereIn('site_id', $siteIds)
            ->whereBetween('metric_date', [$prevMonthStart->toDateString(), $prevMonthEnd->toDateString()])
            ->sum('page_views');

        return [
            'active_sites' => $activeSites,
            'sites_delta' => $sitesDelta,
            'users' => $users,
            'users_delta_pct' => $this->pctGrowth($users, $usersPrev),
            'published' => $published,
            'published_delta_pct' => $this->pctGrowth($publishedThisMonth, $publishedPrevMonth),
            'views' => $views,
            'views_delta_pct' => $this->pctGrowth($viewsThisMonth, $viewsPrevMonth),
        ];
    }

    /**
     * @return array{today: array{label:string, date:string, items:list<array<string,mixed>>}, tomorrow: array{label:string, date:string, items:list<array<string,mixed>>}}
     */
    public function newsQueue(User $user): array
    {
        $siteIds = $this->siteIds($user);
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        return [
            'today' => [
                'label' => 'امروز · '.PersianDate::format($today, 'l'),
                'date' => PersianDate::format($today, 'j F Y'),
                'items' => $this->queueForDay($siteIds, $today),
            ],
            'tomorrow' => [
                'label' => 'فردا · '.PersianDate::format($tomorrow, 'l'),
                'date' => PersianDate::format($tomorrow, 'j F Y'),
                'items' => $this->queueForDay($siteIds, $tomorrow),
            ],
        ];
    }

    /**
     * @param  list<int>  $siteIds
     * @return list<array<string,mixed>>
     */
    protected function queueForDay(array $siteIds, Carbon $day): array
    {
        $start = $day->copy()->startOfDay();
        $end = $day->copy()->endOfDay();
        $isToday = $day->isSameDay(now());

        $scheduled = Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->with(['category', 'author', 'creator'])
            ->whereIn('site_id', $siteIds)
            ->whereBetween('scheduled_for', [$start, $end])
            ->orderBy('scheduled_for')
            ->get();

        $pipeline = collect();
        if ($isToday) {
            $pipeline = Article::query()
                ->withoutGlobalScopes([SiteScope::class])
                ->with(['category', 'author', 'creator'])
                ->whereIn('site_id', $siteIds)
                ->whereIn('status', [ArticleStatus::Approved, ArticleStatus::InReview, ArticleStatus::Draft])
                ->where(function ($q) use ($start, $end): void {
                    $q->whereBetween('updated_at', [$start, $end])
                        ->orWhereNull('scheduled_for');
                })
                ->whereNotIn('id', $scheduled->pluck('id'))
                ->orderByRaw("CASE status WHEN 'approved' THEN 1 WHEN 'in_review' THEN 2 WHEN 'draft' THEN 3 ELSE 4 END")
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get();
        }

        $merged = $scheduled->concat($pipeline)->unique('id')->take(8)->values();

        return $merged->values()->map(function (Article $article, int $index) use ($day): array {
            $when = $article->scheduled_for ?? $article->updated_at ?? $day;
            $startTime = $when->copy();
            $endTime = $when->copy()->addMinutes(30);
            $statusKey = $this->queueStatusKey($article, $day);

            return [
                'id' => $article->id,
                'num' => PersianDate::persianDigits(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)),
                'title' => $article->title,
                'summary' => $article->summary ?: str($article->body)->stripTags()->limit(80)->toString(),
                'time' => PersianDate::persianDigits($startTime->format('H:i')).' تا '.PersianDate::persianDigits($endTime->format('H:i')),
                'category' => $article->category?->name ?? 'عمومی',
                'status' => $this->queueStatusLabel($statusKey),
                'status_key' => $statusKey,
                'author' => $article->author?->name ?? $article->creator?->name ?? 'تحریریه',
                'description' => str($article->body)->stripTags()->limit(280)->toString(),
                'note' => $article->editor_note
                    ?: ($article->rejection_reason ? 'دلیل رد: '.$article->rejection_reason : $this->defaultNote($statusKey)),
                'day_label' => ($day->isSameDay(now()) ? 'امروز' : 'فردا').' · '.PersianDate::format($day, 'l j F Y'),
                'edit_url' => \App\Filament\Resources\Articles\ArticleResource::getUrl('edit', ['record' => $article]),
            ];
        })->all();
    }

    protected function queueStatusKey(Article $article, Carbon $day): string
    {
        if ($article->status === ArticleStatus::Scheduled && $article->scheduled_for && $article->scheduled_for->lt(now()) && $day->isToday()) {
            return 'urgent';
        }

        return match ($article->status) {
            ArticleStatus::Approved, ArticleStatus::Published => 'ready',
            ArticleStatus::InReview => 'review',
            ArticleStatus::Scheduled => 'scheduled',
            ArticleStatus::Draft => 'scheduled',
            default => 'review',
        };
    }

    protected function queueStatusLabel(string $key): string
    {
        return match ($key) {
            'ready' => 'آماده انتشار',
            'review' => 'در صف بررسی',
            'urgent' => 'معوق',
            'scheduled' => 'پیش‌نویس',
            default => 'در صف',
        };
    }

    protected function defaultNote(string $statusKey): string
    {
        return match ($statusKey) {
            'ready' => 'عنوان و لید بررسی شده است؛ پس از کنترل نهایی منتشر شود.',
            'review' => 'نیازمند تأیید دبیر سرویس و تکمیل جزئیات.',
            'urgent' => 'زمان انتشار گذشته است؛ بازتنظیم زمان‌بندی لازم است.',
            default => 'محتوا در جریان آماده‌سازی تحریریه است.',
        };
    }

    /**
     * @return array{labels:list<string>, data:list<int>, max:int}
     */
    public function visitsChart(User $user, int $days = 30): array
    {
        $siteIds = $this->siteIds($user);
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = SiteDailyMetric::query()
            ->whereIn('site_id', $siteIds)
            ->where('metric_date', '>=', $start->toDateString())
            ->selectRaw('metric_date, SUM(page_views) as aggregate')
            ->groupBy('metric_date')
            ->pluck('aggregate', 'metric_date');

        // Fallback: approximate from published articles per day when no metrics yet.
        if ($rows->sum() === 0) {
            $rows = Article::query()
                ->withoutGlobalScopes([SiteScope::class])
                ->whereIn('site_id', $siteIds)
                ->where('status', ArticleStatus::Published)
                ->where('published_at', '>=', $start)
                ->selectRaw('DATE(published_at) as day, COUNT(*) * 120 as aggregate')
                ->groupBy('day')
                ->pluck('aggregate', 'day');
        }

        $labels = [];
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->toDateString();
            $labels[] = PersianDate::format($day, 'j F');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'max' => max(1, ...( $data ?: [1])),
        ];
    }

    /**
     * @return array{active:int, setup:int, inactive:int, sites:list<array{name:string,status:string}>}
     */
    public function provincialSites(User $user): array
    {
        $siteIds = $this->siteIds($user);
        $sites = Site::query()
            ->whereIn('id', $siteIds)
            ->where('type', SiteType::Regional)
            ->orderBy('name')
            ->get();

        $active = $sites->where('is_active', true)->count();
        $inactive = $sites->where('is_active', false)->count();
        $setup = $sites->filter(function (Site $site): bool {
            return $site->is_active && blank($site->theme_id);
        })->count();

        return [
            'active' => $active,
            'setup' => $setup,
            'inactive' => $inactive,
            'sites' => $sites->map(fn (Site $site): array => [
                'name' => $site->name,
                'status' => ! $site->is_active ? 'inactive' : (blank($site->theme_id) ? 'setup' : 'active'),
            ])->all(),
        ];
    }

    /**
     * @return array{score:int, visitors:int, content:int, engagement:int, growth_pct:float}
     */
    public function performance(User $user): array
    {
        $stats = $this->headlineStats($user);
        $siteIds = $this->siteIds($user);

        $publishedShare = min(100, (int) round(($stats['published'] > 0 ? 42 : 10) + min(30, $stats['published'] / 10)));
        $contentShare = min(100, 20 + Media::query()->withoutGlobalScopes([SiteScope::class])->whereIn('site_id', $siteIds)->count());
        $engagementShare = min(100, 15 + (int) round($stats['views_delta_pct']));

        $visitors = (int) round(($publishedShare / ($publishedShare + $contentShare + $engagementShare + 1)) * 100);
        $content = (int) round(($contentShare / ($publishedShare + $contentShare + $engagementShare + 1)) * 100);
        $engagement = max(0, 100 - $visitors - $content);
        $score = (int) min(99, max(40, round(($stats['active_sites'] * 8) + ($stats['published_delta_pct'] / 2) + 50)));

        return [
            'score' => $score,
            'visitors' => $visitors,
            'content' => $content,
            'engagement' => $engagement,
            'growth_pct' => max($stats['views_delta_pct'], $stats['published_delta_pct']),
        ];
    }

    /**
     * @return list<array{icon:string, text:string, time:string}>
     */
    public function recentActivities(User $user, int $limit = 6): array
    {
        $siteIds = $this->siteIds($user);

        $rows = Activity::query()
            ->latest()
            ->limit(40)
            ->get()
            ->filter(function (Activity $activity) use ($user, $siteIds): bool {
                if ($user->isGlobalOperator()) {
                    return true;
                }
                $sid = data_get($activity->properties, 'site_id');

                return $sid === null || in_array((int) $sid, $siteIds, true);
            })
            ->take($limit);

        return $rows->map(function (Activity $activity): array {
            $icon = match ($activity->log_name) {
                'editorial' => '▤',
                'access' => '♙',
                'notifications' => '♧',
                default => '◎',
            };

            return [
                'icon' => $icon,
                'text' => $activity->description,
                'time' => PersianDate::relative($activity->created_at),
            ];
        })->values()->all();
    }

    /**
     * @return list<array{title:string, summary:string, url:string, thumb:string}>
     */
    public function latestNews(User $user, int $limit = 2): array
    {
        $siteIds = $this->siteIds($user);

        return Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds)
            ->where('status', ArticleStatus::Published)
            ->latest('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Article $article): array => [
                'title' => $article->title,
                'summary' => $article->summary ?: str($article->body)->stripTags()->limit(110)->toString(),
                'url' => \App\Filament\Resources\Articles\ArticleResource::getUrl('edit', ['record' => $article]),
                'thumb' => '▣',
            ])
            ->all();
    }

    /**
     * @return list<array{label:string, url:string, icon:string}>
     */
    public function quickActions(User $user): array
    {
        $actions = [
            ['label' => 'مدیریت سایت‌ها', 'url' => \App\Filament\Resources\Sites\SiteResource::getUrl('index'), 'icon' => '◎', 'permission' => 'sites.view'],
            ['label' => 'مدیریت کاربران', 'url' => \App\Filament\Resources\Users\UserResource::getUrl('index'), 'icon' => '♙', 'permission' => 'users.view'],
            ['label' => 'تنظیمات', 'url' => \App\Filament\Resources\SiteSettings\SiteSettingResource::getUrl('index'), 'icon' => '⚙', 'permission' => 'settings.manage'],
            ['label' => 'گزارش‌گیری', 'url' => \App\Filament\Pages\ReportsOverview::getUrl(), 'icon' => '▥', 'permission' => 'reports.view'],
            ['label' => 'پشتیبانی', 'url' => \App\Filament\Pages\SupportCenter::getUrl(), 'icon' => '☏', 'permission' => null],
            ['label' => 'ثبت خبر', 'url' => \App\Filament\Resources\Articles\ArticleResource::getUrl('create'), 'icon' => '▤', 'permission' => 'articles.create'],
        ];

        return collect($actions)
            ->filter(function (array $action) use ($user): bool {
                if ($user->isGlobalOperator() || $action['permission'] === null) {
                    return true;
                }
                foreach ($user->authorizedSiteIds() as $siteId) {
                    if ($user->hasSitePermission($action['permission'], (int) $siteId)) {
                        return true;
                    }
                }

                return $user->can($action['permission']);
            })
            ->map(fn (array $action): array => [
                'label' => $action['label'],
                'url' => $action['url'],
                'icon' => $action['icon'],
            ])
            ->values()
            ->all();
    }

    protected function pctGrowth(float|int $current, float|int $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
