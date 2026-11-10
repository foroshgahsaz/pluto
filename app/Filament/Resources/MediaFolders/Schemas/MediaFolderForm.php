<?php

namespace App\Filament\Resources\MediaFolders\Schemas;

use App\Models\MediaFolder;
use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaFolderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('سایت')
                ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->live()
                ->searchable(),
            Select::make('parent_id')
                ->label('پوشه والد')
                ->options(function (callable $get) {
                    $siteId = $get('site_id');

                    return MediaFolder::query()
                        ->withoutGlobalScopes()
                        ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
                        ->orderBy('name')
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->nullable(),
            TextInput::make('name')
                ->label('نام پوشه')
                ->required()
                ->maxLength(255),
        ]);
    }
}
