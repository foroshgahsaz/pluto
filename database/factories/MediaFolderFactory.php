<?php

namespace Database\Factories;

use App\Models\MediaFolder;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaFolder>
 */
class MediaFolderFactory extends Factory
{
    protected $model = MediaFolder::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'parent_id' => null,
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
