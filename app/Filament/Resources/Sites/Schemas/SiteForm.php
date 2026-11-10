<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Enums\SiteType;
use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('نوع سایت')
                    ->options(collect(SiteType::cases())->mapWithKeys(
                        fn (SiteType $type): array => [$type->value => $type->label()]
                    ))
                    ->required(),
                TextInput::make('name')
                    ->label('نام')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('اسلاگ (زیرمسیر)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                Select::make('parent_id')
                    ->label('سایت والد')
                    ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                TextInput::make('locale')
                    ->label('زبان')
                    ->default('fa')
                    ->required(),
                TextInput::make('timezone')
                    ->label('منطقه زمانی')
                    ->default('Asia/Tehran')
                    ->required(),
                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }
}
