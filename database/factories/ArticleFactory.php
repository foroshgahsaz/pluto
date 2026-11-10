<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'site_id' => Site::factory(),
            'category_id' => null,
            'author_id' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'summary' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'status' => ArticleStatus::Draft,
            'is_featured' => false,
            'is_selected' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Article $article): void {
            if (! $article->category_id) {
                $article->category_id = Category::factory()->create([
                    'site_id' => $article->site_id,
                ])->id;
            }

            if (! $article->author_id) {
                $article->author_id = Author::factory()->create([
                    'site_id' => $article->site_id,
                ])->id;
            }
        });
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ArticleStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => ArticleStatus::Scheduled,
            'scheduled_for' => now()->subMinute(),
        ]);
    }
}
