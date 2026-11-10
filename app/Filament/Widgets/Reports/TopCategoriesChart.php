<?php

namespace App\Filament\Widgets\Reports;

use App\Filament\Widgets\ArticleStatsOverview;
use App\Models\User;
use App\Services\Dashboard\ReportsService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopCategoriesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'پرتکرارترین دسته‌ها';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return ArticleStatsOverview::canView();
    }

    protected function getData(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $siteId = filled($this->pageFilters['site_id'] ?? null) ? (int) $this->pageFilters['site_id'] : null;
        $from = filled($this->pageFilters['from'] ?? null) ? Carbon::parse($this->pageFilters['from'])->startOfDay() : null;
        $to = filled($this->pageFilters['to'] ?? null) ? Carbon::parse($this->pageFilters['to'])->endOfDay() : null;

        $rows = app(ReportsService::class)->topCategories($user, $siteId, 8, $from, $to);

        return [
            'datasets' => [
                [
                    'label' => 'تعداد خبر',
                    'data' => $rows->pluck('aggregate')->map(fn ($v) => (int) $v)->all(),
                    'backgroundColor' => '#0f766e',
                ],
            ],
            'labels' => $rows->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
