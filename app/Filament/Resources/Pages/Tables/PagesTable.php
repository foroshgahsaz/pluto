<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Enums\PageStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('title')->label('عنوان')->searchable(),
                TextColumn::make('slug')->label('اسلاگ'),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => $state instanceof PageStatus ? $state->label() : (string) $state)
                    ->badge(),
                TextColumn::make('updated_at')->label('به‌روزرسانی')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('site_id')->label('سایت')->relationship('site', 'name'),
                SelectFilter::make('status')->label('وضعیت')->options(
                    collect(PageStatus::cases())->mapWithKeys(
                        fn (PageStatus $s) => [$s->value => $s->label()]
                    )->all()
                ),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
