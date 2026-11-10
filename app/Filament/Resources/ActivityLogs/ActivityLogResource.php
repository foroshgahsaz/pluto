<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationLabel = 'لاگ فعالیت';

    protected static ?string $modelLabel = 'رویداد';

    protected static ?string $pluralModelLabel = 'لاگ فعالیت';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'امنیت و عملیات';

    protected static ?int $navigationSort = 80;

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('activity_logs.view', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('زمان')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('log_name')->label('دسته')->badge(),
                TextColumn::make('description')->label('رویداد')->searchable(),
                TextColumn::make('causer.name')->label('کاربر')->placeholder('سیستم'),
                TextColumn::make('subject_type')->label('موضوع')->toggleable(),
                TextColumn::make('properties.site_id')->label('سایت')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('دسته')
                    ->options([
                        'editorial' => 'تحریریه',
                        'media' => 'رسانه',
                        'forms' => 'فرم‌ها',
                        'backup' => 'بکاپ',
                        'access' => 'دسترسی',
                        'sites' => 'سایت‌ها',
                        'settings' => 'تنظیمات',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        /** @var User|null $user */
        $user = auth()->user();
        if ($user && ! $user->isSuperAdmin()) {
            $siteIds = array_map('intval', $user->authorizedSiteIds() ?: [-1]);
            $query->where(function ($q) use ($siteIds): void {
                foreach ($siteIds as $siteId) {
                    $q->orWhere('properties->site_id', $siteId);
                }
            });
        }

        return $query;
    }
}
