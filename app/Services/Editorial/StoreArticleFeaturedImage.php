<?php

namespace App\Services\Editorial;

use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class StoreArticleFeaturedImage
{
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

        $media = Media::query()->create([
            'site_id' => $article->site_id,
            'uploaded_by' => $actor?->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => basename($path),
            'mime_type' => $disk->mimeType($path) ?: null,
            'extension' => pathinfo($path, PATHINFO_EXTENSION) ?: null,
            'size' => $disk->size($path) ?: 0,
            'visibility' => 'public',
            'checksum' => hash('sha256', $disk->get($path)),
        ]);

        $article->forceFill(['featured_media_id' => $media->id])->save();
    }
}
