<?php

namespace App\Filament\Resources\HomepageSections\Schemas;

use App\Enums\HomepageSectionType;
use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HomepageSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('سایت')
                ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('key')->label('کلید')->required()->helperText('شناسه یکتا در هر سایت، مثلاً latest'),
            TextInput::make('title')->label('عنوان')->required(),
            Select::make('type')
                ->label('نوع')
                ->options(collect(HomepageSectionType::cases())->mapWithKeys(
                    fn (HomepageSectionType $t) => [$t->value => $t->label()]
                ))
                ->required(),
            TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
            Toggle::make('is_active')->label('فعال')->default(true),
            Textarea::make('config_json')
                ->label('پیکربندی (JSON)')
                ->rows(5)
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
