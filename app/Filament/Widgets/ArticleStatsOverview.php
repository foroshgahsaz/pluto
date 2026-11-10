<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Services\Dashboard\EditorialDashboardService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ArticleStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'آمار اخبار';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return static::viewerCanSeeReports();
    }

    protected function getStats(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $counts = app(EditorialDashboardService::class)->articleCounts($user);

        return [
            Stat::make('کل اخبار', number_format($counts['total']))
                ->description('در سایت‌های مجاز شما')
                ->color('gray'),
            Stat::make('منتشر شده', number_format($counts['published']))
                ->description('وضعیت انتشار')
                ->color('success'),
            Stat::make('پیش‌نویس', number_format($counts['draft']))
                ->color('warning'),
            Stat::make('در حال بررسی', number_format($counts['in_review']))
                ->color('info'),
            Stat::make('زمان‌بندی شده', number_format($counts['scheduled']))
                ->color('primary'),
        ];
    }

    protected static function viewerCanSeeReports(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->isGlobalOperator()) {
            return true;
        }
        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('reports.view', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }
}
