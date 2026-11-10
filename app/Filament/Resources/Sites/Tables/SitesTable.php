<?php

namespace App\Filament\Resources\Sites\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام')->searchable(),
                TextColumn::make('slug')->label('اسلاگ')->searchable(),
                TextColumn::make('type')->label('نوع')->badge(),
                IconColumn::make('is_active')->label('فعال')->boolean(),
                TextColumn::make('updated_at')->label('به‌روزرسانی')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
