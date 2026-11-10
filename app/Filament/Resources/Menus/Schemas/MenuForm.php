<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Support\Tenancy\AuthorizedSiteOptions;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('سایت')
                ->options(fn () => AuthorizedSiteOptions::pluck())
                ->required()
                ->searchable(),
            TextInput::make('name')->label('نام')->required(),
            TextInput::make('location')->label('محل نمایش')->required()->helperText('مثلاً primary یا secondary'),
        ]);
    }
}
