<?php

namespace App\Filament\Resources\HomepageSections\Tables;

use App\Enums\HomepageSectionType;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HomepageSectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('site.name')->label('سایت'),
                TextColumn::make('title')->label('عنوان')->searchable(),
                TextColumn::make('type')
                    ->label('نوع')
                    ->formatStateUsing(fn ($state) => $state instanceof HomepageSectionType ? $state->label() : (string) $state)
                    ->badge(),
                TextColumn::make('sort_order')->label('ترتیب')->sortable(),
                IconColumn::make('is_active')->label('فعال')->boolean(),
            ])
            ->filters([
                SelectFilter::make('site_id')->label('سایت')->relationship('site', 'name'),
                SelectFilter::make('type')->label('نوع')->options(
                    collect(HomepageSectionType::cases())->mapWithKeys(
                        fn (HomepageSectionType $t) => [$t->value => $t->label()]
                    )->all()
                ),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
