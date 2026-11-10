<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'user_id' => null,
            'display_name' => fake()->name(),
            'bio' => fake()->optional()->sentence(),
            'avatar_path' => null,
            'is_active' => true,
        ];
    }
}
