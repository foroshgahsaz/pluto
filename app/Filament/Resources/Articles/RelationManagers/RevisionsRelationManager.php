<?php

namespace App\Filament\Resources\Articles\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'نسخه‌ها';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')->label('نسخه'),
                TextColumn::make('user.name')->label('کاربر'),
                TextColumn::make('note')->label('یادداشت')->limit(40),
                TextColumn::make('created_at')->label('زمان')->dateTime('Y-m-d H:i'),
            ])
            ->defaultSort('version', 'desc')
            ->paginated([10, 25]);
    }
}
