<?php

namespace App\Filament\Resources\MediaFolders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaFoldersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('name')->label('نام')->searchable(),
                TextColumn::make('parent.name')->label('والد'),
                TextColumn::make('media_count')->counts('media')->label('تعداد فایل'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
