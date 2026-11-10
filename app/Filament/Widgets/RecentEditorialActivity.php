<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Services\Dashboard\EditorialDashboardService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class RecentEditorialActivity extends TableWidget
{
    protected static ?string $heading = 'آخرین فعالیت تحریریه';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ArticleStatsOverview::canView();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->activityQuery())
            ->columns([
                TextColumn::make('created_at')->label('زمان')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('description')->label('رویداد'),
                TextColumn::make('causer.name')->label('کاربر')->placeholder('سیستم'),
                TextColumn::make('properties.site_id')->label('سایت')->placeholder('—'),
                TextColumn::make('properties.from')->label('از')->placeholder('—'),
                TextColumn::make('properties.to')->label('به')->placeholder('—'),
            ])
            ->paginated([10, 15, 25])
            ->defaultPaginationPageOption(10);
    }

    protected function activityQuery(): Builder
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return Activity::query()->whereRaw('1 = 0');
        }

        $siteIds = array_map('intval', app(EditorialDashboardService::class)->authorizedSiteIds($user) ?: [-1]);

        return Activity::query()
            ->where('log_name', 'editorial')
            ->where(function (Builder $query) use ($siteIds): void {
                foreach ($siteIds as $siteId) {
                    $query->orWhere('properties->site_id', $siteId);
                }
            })
            ->latest();
    }
}
