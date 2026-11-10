<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                    ->label('سایت')
                    ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->live(),
                Select::make('parent_id')
                    ->label('دسته والد')
                    ->options(function (callable $get) {
                        $siteId = $get('site_id');

                        return \App\Models\Category::query()
                            ->withoutGlobalScopes()
                            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->nullable(),
                TextInput::make('name')
                    ->label('نام')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $get): void {
                        if (! $get('slug')) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('اسلاگ')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('توضیحات')
                    ->rows(3),
                TextInput::make('sort_order')
                    ->label('ترتیب')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }
}
