<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('name')->label('نام')->searchable(),
                TextColumn::make('location')->label('محل')->badge(),
                TextColumn::make('all_items_count')->counts('allItems')->label('آیتم‌ها'),
            ])
            ->filters([
                SelectFilter::make('site_id')->label('سایت')->relationship('site', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
