<?php

namespace App\Filament\Resources\Authors\Schemas;

use App\Models\Site;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('سایت')
                ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('display_name')
                ->label('نام نمایشی')
                ->required(),
            Select::make('user_id')
                ->label('کاربر مرتبط')
                ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->nullable(),
            Textarea::make('bio')
                ->label('بیوگرافی')
                ->rows(4),
            Toggle::make('is_active')
                ->label('فعال')
                ->default(true),
        ]);
    }
}
