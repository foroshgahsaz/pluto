<?php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('display_name')->label('نام')->searchable(),
                TextColumn::make('user.name')->label('کاربر'),
                IconColumn::make('is_active')->label('فعال')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
