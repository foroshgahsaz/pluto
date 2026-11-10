<?php

namespace App\Services\Dashboard;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\FormSubmission;
use App\Models\LoginEvent;
use App\Models\Media;
use App\Models\Site;
use App\Models\User;
use App\Support\Tenancy\SiteScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportsService
{
    public function __construct(
        protected EditorialDashboardService $editorial,
    ) {}

    /**
     * @return list<int>
     */
    public function resolveSiteIds(User $user, ?int $siteId = null): array
    {
        $authorized = array_map('intval', $this->editorial->authorizedSiteIds($user));
        if ($siteId === null) {
            return $authorized;
        }

        return in_array($siteId, $authorized, true) ? [$siteId] : [-1];
    }

    /**
     * @param  list<int>  $siteIds
     * @return array{
     *     articles: array{total:int,published:int,draft:int,in_review:int,scheduled:int},
     *     form_submissions: int,
     *     media_files: int,
     *     media_bytes: int,
     *     login_success: int,
     *     login_failed: int,
     *     active_users: int
     * }
     */
    public function overview(User $user, ?int $siteId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $siteIds = $this->resolveSiteIds($user, $siteId);
        $articles = $this->editorial->articleCounts($user, $siteIds, $from, $to);

        $submissions = FormSubmission::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds ?: [-1]);
        if ($from) {
            $submissions->where('created_at', '>=', $from);
        }
        if ($to) {
            $submissions->where('created_at', '<=', $to);
        }

        $media = Media::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('site_id', $siteIds ?: [-1]);
        if ($from) {
            $media->where('created_at', '>=', $from);
        }
        if ($to) {
            $media->where('created_at', '<=', $to);
        }

        $loginBase = LoginEvent::query();
        if ($from) {
            $loginBase->where('created_at', '>=', $from);
        }
        if ($to) {
            $loginBase->where('created_at', '<=', $to);
        }

        $activeUsers = User::query()
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->when(
                ! $user->isSuperAdmin(),
                fn ($q) => $q->whereHas('sites', fn ($s) => $s->whereIn('sites.id', $siteIds ?: [-1]))
            )
            ->count();

        return [
            'articles' => $articles,
            'form_submissions' => (int) $submissions->count(),
            'media_files' => (int) (clone $media)->count(),
            'media_bytes' => (int) (clone $media)->sum('size'),
            'login_success' => (int) (clone $loginBase)->where('event', 'login_success')->count(),
            'login_failed' => (int) (clone $loginBase)->where('event', 'login_failed')->count(),
            'active_users' => $activeUsers,
        ];
    }

    /**
     * @param  list<int>  $siteIds
     * @return Collection<int, object{site_id:int, site_name:string, published:int, draft:int, total:int}>
     */
    public function articlesBySite(User $user, ?int $siteId = null, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $siteIds = $this->resolveSiteIds($user, $siteId);

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
            ->selectRaw('site_id, status, COUNT(*) as aggregate')
            ->groupBy('site_id', 'status')
            ->get();

        $names = Site::query()->whereIn('id', $siteIds ?: [-1])->pluck('name', 'id');

        return collect($siteIds)->map(function (int $id) use ($rows, $names) {
            $siteRows = $rows->where('site_id', $id);
            $published = (int) $siteRows->where('status', ArticleStatus::Published->value)->sum('aggregate');
            $draft = (int) $siteRows->where('status', ArticleStatus::Draft->value)->sum('aggregate');
            $total = (int) $siteRows->sum('aggregate');

            return (object) [
                'site_id' => $id,
                'site_name' => (string) ($names[$id] ?? ('#'.$id)),
                'published' => $published,
                'draft' => $draft,
                'total' => $total,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * @return Collection<int, object{name:string, aggregate:int}>
     */
    public function topCategories(User $user, ?int $siteId = null, int $limit = 10, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $siteIds = $this->resolveSiteIds($user, $siteId);

        $query = Article::query()
            ->withoutGlobalScopes([SiteScope::class])
            ->whereIn('articles.site_id', $siteIds ?: [-1])
            ->whereNotNull('category_id')
            ->join('categories', 'categories.id', '=', 'articles.category_id');

        if ($from) {
            $query->where('articles.created_at', '>=', $from);
        }
        if ($to) {
            $query->where('articles.created_at', '<=', $to);
        }

        return $query
            ->selectRaw('categories.name as name, COUNT(*) as aggregate')
            ->groupBy('categories.name')
            ->orderByDesc('aggregate')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    public function publishVolume(User $user, int $days = 30, ?int $siteId = null): array
    {
        return $this->editorial->publishVolumeByDay($user, $days, $this->resolveSiteIds($user, $siteId));
    }
}
