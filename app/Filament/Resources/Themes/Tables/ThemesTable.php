<?php

namespace App\Filament\Resources\Themes\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ThemesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('نام')->searchable(),
            TextColumn::make('key')->label('کلید'),
            IconColumn::make('is_active')->label('فعال')->boolean(),
            TextColumn::make('sites_count')->counts('sites')->label('سایت‌ها'),
        ])->recordActions([
            EditAction::make(),
        ]);
    }
}
