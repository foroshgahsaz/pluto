<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Models\Site;
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
                ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('name')->label('نام')->required(),
            TextInput::make('location')->label('محل نمایش')->required()->helperText('مثلاً primary یا secondary'),
        ]);
    }
}
