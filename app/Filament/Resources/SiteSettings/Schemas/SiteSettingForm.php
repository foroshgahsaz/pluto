<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('سایت')
                ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            Select::make('group')
                ->label('گروه')
                ->options([
                    'branding' => 'هویت بصری',
                    'colors' => 'رنگ‌ها',
                    'seo' => 'سئو',
                    'general' => 'عمومی',
                ])
                ->required(),
            TextInput::make('key')->label('کلید')->required(),
            Textarea::make('value')
                ->label('مقدار (JSON)')
                ->rows(4)
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $state)
                ->dehydrateStateUsing(function ($state) {
                    if (! filled($state)) {
                        return null;
                    }
                    if (is_array($state)) {
                        return $state;
                    }
                    $decoded = json_decode((string) $state, true);

                    return json_last_error() === JSON_ERROR_NONE ? $decoded : ['value' => $state];
                })
                ->required(),
        ]);
    }
}
