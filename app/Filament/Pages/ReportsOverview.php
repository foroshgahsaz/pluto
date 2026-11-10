<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ArticleStatsOverview;
use App\Filament\Widgets\PublishVolumeChart;
use App\Filament\Widgets\RecentEditorialActivity;
use App\Filament\Widgets\Reports\ArticlesBySiteTable;
use App\Filament\Widgets\Reports\ReportStatsOverview;
use App\Filament\Widgets\Reports\TopCategoriesChart;
use App\Models\Site;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use UnitEnum;

class ReportsOverview extends Page
{
    use HasFiltersAction;

    protected string $view = 'filament.pages.reports-overview';

    protected static ?string $navigationLabel = 'گزارش‌ها و آمار';

    protected static ?string $title = 'گزارش‌ها و آمار';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'امنیت و عملیات';

    protected static ?int $navigationSort = 50;

    public static function canAccess(): bool
    {
        return ArticleStatsOverview::canView();
    }

    /**
     * @return array<class-string<Widget>|WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            ReportStatsOverview::class,
            TopCategoriesChart::class,
            ArticlesBySiteTable::class,
            PublishVolumeChart::class,
            RecentEditorialActivity::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->form([
                    Select::make('site_id')
                        ->label('سایت')
                        ->options(fn () => $this->filterableSites()->pluck('name', 'id'))
                        ->placeholder('همه سایت‌های مجاز')
                        ->searchable(),
                    DatePicker::make('from')
                        ->label('از تاریخ'),
                    DatePicker::make('to')
                        ->label('تا تاریخ'),
                ]),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Site>
     */
    protected function filterableSites()
    {
        /** @var User $user */
        $user = auth()->user();

        return Site::query()
            ->whereIn('id', $user->authorizedSiteIds() ?: [-1])
            ->orderBy('name')
            ->get();
    }
}
