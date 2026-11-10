<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $name = fake()->uuid().'.jpg';

        return [
            'site_id' => Site::factory(),
            'uploaded_by' => null,
            'disk' => 'public',
            'path' => 'articles/'.$name,
            'original_name' => $name,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 1024,
            'visibility' => 'public',
            'checksum' => null,
        ];
    }
}
