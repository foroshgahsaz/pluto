<?php

namespace App\Services\Media;

use App\Contracts\MalwareScanner;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaUploadService
{
    public function __construct(
        protected MalwareScanner $scanner,
    ) {}

    /**
     * @param  array{site_id:int, folder_id?:?int, visibility?:string, category?:?string, alt_text?:?string}  $meta
     */
    public function store(UploadedFile $file, array $meta, ?User $actor = null): Media
    {
        $this->assertSafeUpload($file);

        $visibility = ($meta['visibility'] ?? 'public') === 'private' ? 'private' : 'public';
        $disk = $visibility === 'private'
            ? config('pluto.media.private_disk', 'local')
            : config('pluto.media.public_disk', 'public');

        $extension = strtolower($file->getClientOriginalExtension() ?: ($file->extension() ?: 'bin'));
        $safeName = Str::uuid()->toString().'.'.$extension;
        $directory = 'media/'.($meta['site_id']).'/'.now()->format('Y/m');
        $path = $file->storeAs($directory, $safeName, [
            'disk' => $disk,
            'visibility' => $visibility === 'private' ? 'private' : 'public',
        ]);

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                'file' => 'ذخیره فایل ناموفق بود.',
            ]);
        }

        $absolute = Storage::disk($disk)->path($path);
        $this->scanner->scan($absolute);

        $detectedMime = $this->detectMime($absolute) ?? $file->getMimeType();

        return DB::transaction(function () use ($meta, $actor, $disk, $path, $file, $extension, $detectedMime, $visibility, $absolute): Media {
            $media = Media::query()->create([
                'site_id' => $meta['site_id'],
                'folder_id' => $meta['folder_id'] ?? null,
                'uploaded_by' => $actor?->id,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $this->sanitizeOriginalName($file->getClientOriginalName()),
                'mime_type' => $detectedMime,
                'extension' => $extension,
                'size' => Storage::disk($disk)->size($path) ?: 0,
                'visibility' => $visibility,
                'category' => $meta['category'] ?? $this->guessCategory($detectedMime),
                'alt_text' => $meta['alt_text'] ?? null,
                'checksum' => hash_file('sha256', $absolute) ?: null,
            ]);

            activity('media')
                ->causedBy($actor)
                ->performedOn($media)
                ->withProperties(['site_id' => $media->site_id, 'visibility' => $media->visibility])
                ->log('media.uploaded');

            return $media;
        });
    }

    public function delete(Media $media, ?User $actor = null): void
    {
        DB::transaction(function () use ($media, $actor): void {
            Storage::disk($media->disk)->delete($media->path);
            $media->delete();

            activity('media')
                ->causedBy($actor)
                ->performedOn($media)
                ->withProperties(['site_id' => $media->site_id])
                ->log('media.deleted');
        });
    }

    protected function assertSafeUpload(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        $blocked = config('pluto.media.blocked_extensions', []);

        if ($extension !== '' && in_array($extension, $blocked, true)) {
            throw ValidationException::withMessages([
                'file' => 'این نوع فایل مجاز نیست.',
            ]);
        }

        $tmp = $file->getRealPath();
        $mime = $tmp ? ($this->detectMime($tmp) ?? $file->getMimeType()) : $file->getMimeType();
        $allowed = array_merge(
            config('pluto.media.allowed_image_mimes', []),
            config('pluto.media.allowed_document_mimes', []),
        );

        if (! is_string($mime) || ! in_array($mime, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => 'نوع فایل شناسایی‌شده مجاز نیست.',
            ]);
        }

        $sizeKb = (int) ceil($file->getSize() / 1024);
        $maxKb = str_starts_with($mime, 'image/')
            ? (int) config('pluto.media.max_image_kb', 5120)
            : (int) config('pluto.media.max_document_kb', 20480);

        if ($sizeKb > $maxKb) {
            throw ValidationException::withMessages([
                'file' => "حجم فایل بیش از حد مجاز ({$maxKb} کیلوبایت) است.",
            ]);
        }
    }

    protected function detectMime(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($path) ?: null;
    }

    protected function sanitizeOriginalName(string $name): string
    {
        $name = basename(str_replace(["\0", '\\'], '', $name));
        $name = preg_replace('/[^\pL\pN\.\-\_\s]+/u', '_', $name) ?: 'file';

        return Str::limit($name, 180, '');
    }

    protected function guessCategory(?string $mime): string
    {
        if (is_string($mime) && str_starts_with($mime, 'image/')) {
            return 'image';
        }

        return 'document';
    }
}
