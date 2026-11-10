<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'site_id' => Site::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'body' => fake()->paragraphs(2, true),
            'status' => PageStatus::Draft,
            'published_at' => null,
        ];
    }
}
