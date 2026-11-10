<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('name')->label('نام')->searchable(),
                TextColumn::make('slug')->label('اسلاگ'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
