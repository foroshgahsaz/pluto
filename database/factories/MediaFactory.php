<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $name = fake()->uuid().'.jpg';
        $path = 'media/test/'.$name;

        return [
            'site_id' => Site::factory(),
            'folder_id' => null,
            'uploaded_by' => null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $name,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 1024,
            'visibility' => 'public',
            'category' => 'image',
            'alt_text' => null,
            'checksum' => null,
        ];
    }

    public function private(): static
    {
        return $this->state(fn (): array => [
            'disk' => 'local',
            'visibility' => 'private',
            'path' => 'media/private/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'category' => 'document',
            'original_name' => 'secret.pdf',
        ]);
    }

    public function withStoredFile(): static
    {
        return $this->afterCreating(function (Media $media): void {
            Storage::disk($media->disk)->put($media->path, 'dummy-content');
        });
    }
}
