<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Support\Tenancy\AuthorizedSiteOptions;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('سایت')
                ->options(fn () => AuthorizedSiteOptions::pluck())
                ->required()
                ->searchable(),
            TextInput::make('name')
                ->label('نام')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')
                ->label('اسلاگ')
                ->required(),
        ]);
    }
}
