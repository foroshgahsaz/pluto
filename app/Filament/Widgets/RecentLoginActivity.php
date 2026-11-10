<?php

namespace App\Filament\Widgets;

use App\Models\LoginEvent;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentLoginActivity extends TableWidget
{
    protected static ?string $heading = 'آخرین رویدادهای ورود';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->can('activity_logs.view') || ArticleStatsOverview::canView();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->eventsQuery())
            ->columns([
                TextColumn::make('created_at')->label('زمان')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('event')->label('رویداد')->badge(),
                TextColumn::make('method')->label('روش')->placeholder('—'),
                TextColumn::make('mobile')->label('موبایل')->placeholder('—'),
                TextColumn::make('email')->label('ایمیل')->placeholder('—'),
                TextColumn::make('ip')->label('IP')->placeholder('—'),
            ])
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10);
    }

    protected function eventsQuery(): Builder
    {
        /** @var User|null $user */
        $user = auth()->user();
        $query = LoginEvent::query()->latest('id');

        if ($user && ! $user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
