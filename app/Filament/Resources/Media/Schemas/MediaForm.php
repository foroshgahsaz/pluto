<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\MediaFolder;
use App\Models\Site;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class MediaForm
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
            Select::make('folder_id')
                ->label('پوشه')
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
            FileUpload::make('upload')
                ->label('فایل')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->disk('local')
                ->directory('tmp-uploads')
                ->visibility('private')
                ->maxSize((int) config('pluto.media.max_document_kb', 20480))
                ->acceptedFileTypes(array_merge(
                    config('pluto.media.allowed_image_mimes', []),
                    config('pluto.media.allowed_document_mimes', []),
                ))
                ->dehydrated(false)
                ->visibleOn('create'),
            ToggleButtons::make('visibility')
                ->label('دسترسی')
                ->options([
                    'public' => 'عمومی',
                    'private' => 'خصوصی',
                ])
                ->inline()
                ->default('public')
                ->required(),
            Select::make('category')
                ->label('دسته‌بندی فایل')
                ->options([
                    'image' => 'تصویر',
                    'document' => 'سند',
                    'attachment' => 'پیوست',
                    'other' => 'سایر',
                ])
                ->nullable(),
            TextInput::make('alt_text')
                ->label('متن جایگزین')
                ->maxLength(255),
            TextInput::make('original_name')
                ->label('نام اصلی')
                ->disabled()
                ->dehydrated(false)
                ->visibleOn('edit'),
            TextInput::make('mime_type')
                ->label('MIME')
                ->disabled()
                ->dehydrated(false)
                ->visibleOn('edit'),
        ]);
    }
}
