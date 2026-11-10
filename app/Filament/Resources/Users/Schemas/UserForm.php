<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\Tenancy\AuthorizedSiteOptions;

use Filament\Forms\Components\Repeater;
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
                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
                Toggle::make('is_blocked')
                    ->label('مسدود')
                    ->default(false),
                Toggle::make('is_super_admin')
                    ->label('توسعه‌دهنده (سوپر ادمین فنی)')
                    ->helperText('دسترسی فنی کامل شامل پشتیبان‌گیری؛ فقط برای تیم توسعه')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->disabled(fn (): bool => ! (auth()->user()?->isSuperAdmin() ?? false)),
                Toggle::make('is_platform_admin')
                    ->label('مدیر کل سامانه')
                    ->helperText('بالاترین سطح محتوایی: مدیریت همه سایت‌ها، کاربران و نقش‌ها (بدون ابزارهای فنی دولوپر)')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->disabled(fn (): bool => ! (auth()->user()?->isSuperAdmin() ?? false)),
                Repeater::make('site_roles')
                    ->label('دسترسی به زیرسایت‌ها و نقش')
                    ->helperText('برای هر سایت یک نقش مشخص کنید (مثلاً regional_admin یا journalist). مدیر کل سامانه به همه سایت‌ها دسترسی دارد و نیازی به این لیست ندارد.')
                    ->schema([
                        Select::make('site_id')
                            ->label('سایت')
                            ->options(fn () => AuthorizedSiteOptions::pluck())
                            ->required()
                            ->searchable()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        Select::make('role')
                            ->label('نقش در این سایت')
                            ->options([
                                'regional_admin' => 'مدیر سایت',
                                'editor_in_chief' => 'سردبیر',
                                'journalist' => 'خبرنگار',
                                'copy_editor' => 'ویراستار',
                            ])
                            ->required(),
                        Toggle::make('is_primary')
                            ->label('سایت اصلی')
                            ->default(false),
                    ])
                    ->default([])
                    ->addActionLabel('افزودن دسترسی سایت')
                    ->columnSpanFull()
                    ->visible(fn (): bool => (bool) (auth()->user()?->can('users.assign_roles') || auth()->user()?->isGlobalOperator()))
                    ->dehydrated(fn (): bool => (bool) (auth()->user()?->can('users.assign_roles') || auth()->user()?->isGlobalOperator())),
            ]);
    }
}
