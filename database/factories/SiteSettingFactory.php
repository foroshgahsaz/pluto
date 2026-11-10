<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'group' => 'general',
            'key' => fake()->unique()->word(),
            'value' => ['value' => fake()->word()],
        ];
    }
}
