<?php

namespace App\Services\Editorial;

use App\Models\Article;
use App\Models\User;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StoreArticleFeaturedImage
{
    public function __construct(
        protected MediaUploadService $uploads,
    ) {}

    public function handle(Article $article, mixed $path, ?User $actor = null): void
    {
        if (is_array($path)) {
            $path = $path[0] ?? null;
        }

        if (! is_string($path) || ! filled($path)) {
            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return;
        }

        $absolute = $disk->path($path);
        $mime = $disk->mimeType($path) ?: null;
        $upload = new UploadedFile(
            $absolute,
            basename($path),
            $mime,
            null,
            true,
        );

        try {
            $media = $this->uploads->store($upload, [
                'site_id' => (int) $article->site_id,
                'visibility' => 'public',
                'category' => 'image',
                'alt_text' => $article->title,
            ], $actor);
        } catch (ValidationException $e) {
            $disk->delete($path);
            throw $e;
        }

        // Remove Filament's temporary upload path after re-store through hardened pipeline.
        if ($media->path !== $path) {
            $disk->delete($path);
        }

        $article->forceFill(['featured_media_id' => $media->id])->save();
    }
}
