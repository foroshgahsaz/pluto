<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('group')->label('گروه')->badge(),
                TextColumn::make('key')->label('کلید')->searchable(),
                TextColumn::make('value')
                    ->label('مقدار')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : (string) $state)
                    ->limit(40),
            ])
            ->filters([
                SelectFilter::make('group')->label('گروه')->options([
                    'branding' => 'هویت بصری',
                    'colors' => 'رنگ‌ها',
                    'seo' => 'سئو',
                    'general' => 'عمومی',
                ]),
                SelectFilter::make('site_id')->label('سایت')->relationship('site', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
