<?php

namespace Database\Factories;

use App\Enums\HomepageSectionType;
use App\Models\HomepageSection;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageSection>
 */
class HomepageSectionFactory extends Factory
{
    protected $model = HomepageSection::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'key' => 'section_'.fake()->unique()->slug(2),
            'title' => fake()->words(3, true),
            'type' => HomepageSectionType::Latest,
            'config_json' => ['limit' => 10],
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
