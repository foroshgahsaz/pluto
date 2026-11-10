<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use App\Enums\MenuItemType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'allItems';

    protected static ?string $title = 'آیتم‌های منو';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('parent_id')
                ->label('والد')
                ->relationship(
                    name: 'parent',
                    titleAttribute: 'title',
                    modifyQueryUsing: fn ($query) => $query->where('menu_id', $this->getOwnerRecord()->getKey()),
                )
                ->nullable()
                ->searchable(),
            TextInput::make('title')->label('عنوان')->required(),
            Select::make('type')
                ->label('نوع')
                ->options(collect(MenuItemType::cases())->mapWithKeys(fn (MenuItemType $t) => [$t->value => $t->label()]))
                ->required()
                ->default(MenuItemType::Url->value)
                ->live(),
            TextInput::make('url')
                ->label('آدرس')
                ->visible(fn ($get) => ($get('type') ?? MenuItemType::Url->value) === MenuItemType::Url->value),
            TextInput::make('reference_id')
                ->label('شناسه مرجع')
                ->numeric()
                ->visible(fn ($get) => in_array($get('type'), [
                    MenuItemType::Category->value,
                    MenuItemType::Page->value,
                    MenuItemType::Article->value,
                ], true)),
            TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
            Toggle::make('is_active')->label('فعال')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')->label('عنوان')->searchable(),
                TextColumn::make('type')->label('نوع')->formatStateUsing(fn ($state) => $state instanceof MenuItemType ? $state->label() : (string) $state)->badge(),
                TextColumn::make('parent.title')->label('والد')->placeholder('—'),
                TextColumn::make('url')->label('آدرس')->limit(30),
                IconColumn::make('is_active')->label('فعال')->boolean(),
                TextColumn::make('sort_order')->label('ترتیب')->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['site_id'] = $this->getOwnerRecord()->site_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
