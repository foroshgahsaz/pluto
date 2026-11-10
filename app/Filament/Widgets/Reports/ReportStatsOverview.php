<?php

namespace App\Filament\Widgets\Reports;

use App\Filament\Widgets\ArticleStatsOverview;
use App\Models\User;
use App\Services\Dashboard\ReportsService;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'خلاصه عملکرد';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return ArticleStatsOverview::canView();
    }

    protected function getStats(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $siteId = filled($this->pageFilters['site_id'] ?? null) ? (int) $this->pageFilters['site_id'] : null;
        $from = filled($this->pageFilters['from'] ?? null) ? Carbon::parse($this->pageFilters['from'])->startOfDay() : null;
        $to = filled($this->pageFilters['to'] ?? null) ? Carbon::parse($this->pageFilters['to'])->endOfDay() : null;

        $overview = app(ReportsService::class)->overview($user, $siteId, $from, $to);
        $bytes = $overview['media_bytes'];
        $sizeLabel = $bytes >= 1048576
            ? number_format($bytes / 1048576, 1).' MB'
            : number_format($bytes / 1024, 1).' KB';

        return [
            Stat::make('کل اخبار', number_format($overview['articles']['total']))
                ->description('منتشر: '.number_format($overview['articles']['published']))
                ->color('primary'),
            Stat::make('ارسال فرم', number_format($overview['form_submissions']))
                ->color('info'),
            Stat::make('رسانه', number_format($overview['media_files']))
                ->description($sizeLabel)
                ->color('gray'),
            Stat::make('کاربران فعال', number_format($overview['active_users']))
                ->description('ورود موفق: '.number_format($overview['login_success']).' / ناموفق: '.number_format($overview['login_failed']))
                ->color('success'),
        ];
    }
}
