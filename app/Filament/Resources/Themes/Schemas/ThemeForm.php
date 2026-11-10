<?php

namespace App\Filament\Resources\Themes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ThemeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label('کلید')->required()->unique(ignoreRecord: true),
            TextInput::make('name')->label('نام')->required(),
            TextInput::make('preview_image')->label('مسیر تصویر پیش‌نمایش')->nullable(),
            Toggle::make('is_active')->label('فعال')->default(true),
            Textarea::make('config_schema_json')
                ->label('اسکیمای تنظیمات (JSON)')
                ->rows(6)
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $state)
                ->dehydrateStateUsing(function ($state) {
                    if (! filled($state)) {
                        return null;
                    }
                    if (is_array($state)) {
                        return $state;
                    }
                    $decoded = json_decode((string) $state, true);

                    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
                }),
        ]);
    }
}
