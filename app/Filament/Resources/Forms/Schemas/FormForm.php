<?php

namespace App\Filament\Resources\Forms\Schemas;

use App\Models\Site;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Forms\Components\Select::make('site_id')
                ->label('سایت')
                ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('title')
                ->label('عنوان فرم')
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
            Textarea::make('success_message')
                ->label('پیام موفقیت')
                ->rows(2)
                ->default('فرم با موفقیت ثبت شد.'),
            Toggle::make('is_active')
                ->label('فعال')
                ->default(true),
            Toggle::make('is_multi_step')
                ->label('فرم چندمرحله‌ای')
                ->default(false),
        ]);
    }
}
