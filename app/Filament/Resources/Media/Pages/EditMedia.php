<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['upload'], $data['disk'], $data['path'], $data['mime_type'], $data['extension'], $data['size'], $data['checksum']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->using(function ($record): void {
                    app(\App\Services\Media\MediaUploadService::class)->delete($record, auth()->user());
                }),
        ];
    }
}
