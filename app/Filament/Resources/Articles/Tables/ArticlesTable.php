<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('عنوان')->searchable()->limit(40),
                TextColumn::make('site.name')->label('سایت')->sortable(),
                TextColumn::make('category.name')->label('دسته'),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (ArticleStatus|string $state): string => $state instanceof ArticleStatus ? $state->label() : ArticleStatus::from($state)->label()),
                IconColumn::make('is_featured')->label('ویژه')->boolean(),
                IconColumn::make('is_selected')->label('منتخب')->boolean(),
                TextColumn::make('published_at')->label('انتشار')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('scheduled_for')->label('زمان‌بندی')->dateTime('Y-m-d H:i')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(collect(ArticleStatus::cases())->mapWithKeys(
                        fn (ArticleStatus $status): array => [$status->value => $status->label()]
                    )),
                SelectFilter::make('site_id')
                    ->label('سایت')
                    ->relationship('site', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
