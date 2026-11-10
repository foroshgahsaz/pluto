<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Theme>
 */
class ThemeFactory extends Factory
{
    protected $model = Theme::class;

    public function definition(): array
    {
        $key = 'theme_'.fake()->unique()->slug(2);

        return [
            'key' => $key,
            'name' => fake()->words(2, true),
            'preview_image' => null,
            'is_active' => true,
            'config_schema_json' => [
                'colors' => ['primary', 'secondary', 'accent'],
            ],
        ];
    }
}
