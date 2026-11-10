<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'site_id' => Site::factory(),
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
