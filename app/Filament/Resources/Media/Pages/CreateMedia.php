<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Services\Media\MediaUploadService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $state = $this->form->getState();
        $upload = $state['upload'] ?? null;

        $file = $this->resolveUploadedFile($upload);

        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'data.upload' => 'انتخاب فایل الزامی است.',
            ]);
        }

        return app(MediaUploadService::class)->store($file, [
            'site_id' => (int) $data['site_id'],
            'folder_id' => $data['folder_id'] ?? null,
            'visibility' => $data['visibility'] ?? 'public',
            'category' => $data['category'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
        ], auth()->user());
    }

    protected function resolveUploadedFile(mixed $upload): ?UploadedFile
    {
        if ($upload instanceof UploadedFile) {
            return $upload;
        }

        if (is_array($upload)) {
            $upload = $upload[0] ?? null;
        }

        if (! is_string($upload) || $upload === '') {
            return null;
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($upload)) {
            return null;
        }

        $absolute = $disk->path($upload);

        return new UploadedFile(
            $absolute,
            basename($upload),
            $disk->mimeType($upload) ?: null,
            null,
            true
        );
    }
}
