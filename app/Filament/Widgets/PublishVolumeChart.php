<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Services\Dashboard\EditorialDashboardService;
use Filament\Widgets\ChartWidget;

class PublishVolumeChart extends ChartWidget
{
    protected ?string $heading = 'حجم انتشار (۱۴ روز اخیر)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ArticleStatsOverview::canView();
    }

    protected function getData(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $volume = app(EditorialDashboardService::class)->publishVolumeByDay($user, 14);

        return [
            'datasets' => [
                [
                    'label' => 'منتشر شده',
                    'data' => $volume['data'],
                    'borderColor' => '#0f766e',
                    'backgroundColor' => 'rgba(15, 118, 110, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $volume['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
