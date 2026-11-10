<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('نام')
                    ->required(),
                TextInput::make('mobile')
                    ->label('موبایل')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('ایمیل')
                    ->email()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('رمز عبور')
                    ->password()
                    ->revealable()
                    ->rule(Password::defaults())
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('sites')
                    ->label('سایت‌های مجاز')
                    ->multiple()
                    ->relationship('sites', 'name')
                    ->preload()
                    ->searchable(),
                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
                Toggle::make('is_blocked')
                    ->label('مسدود')
                    ->default(false),
                Toggle::make('is_super_admin')
                    ->label('سوپر ادمین')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->disabled(fn (): bool => ! (auth()->user()?->isSuperAdmin() ?? false)),
            ]);
    }
}
