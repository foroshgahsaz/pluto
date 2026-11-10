<?php

namespace App\Filament\Resources\Forms\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('عنوان')->searchable(),
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('slug')->label('اسلاگ'),
                IconColumn::make('is_active')->label('فعال')->boolean(),
                IconColumn::make('is_multi_step')->label('چندمرحله‌ای')->boolean(),
                TextColumn::make('fields_count')->counts('fields')->label('فیلدها'),
                TextColumn::make('submissions_count')->counts('submissions')->label('پاسخ‌ها'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
