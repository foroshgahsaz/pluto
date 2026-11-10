<?php

namespace App\Services\Editorial;

use App\Models\Article;
use App\Support\Tenancy\SiteContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Published-article queries for the public website (site-scoped via SiteContext).
 */
class PublicArticleQuery
{
    public function __construct(
        protected SiteContext $siteContext,
    ) {}

    public function latest(int $limit = 10): Collection
    {
        return $this->publishedBase()->latest('published_at')->limit($limit)->get();
    }

    public function featured(int $limit = 5): Collection
    {
        return $this->publishedBase()->where('is_featured', true)->latest('published_at')->limit($limit)->get();
    }

    public function selected(int $limit = 5): Collection
    {
        return $this->publishedBase()->where('is_selected', true)->latest('published_at')->limit($limit)->get();
    }

    public function byCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->publishedBase()
            ->where('category_id', $categoryId)
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        $term = trim($term);

        return $this->publishedBase()
            ->where(function (Builder $query) use ($term): void {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%")
                    ->orWhere('body', 'like', "%{$term}%");
            })
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function findPublishedBySlug(string $slug): ?Article
    {
        return $this->publishedBase()
            ->where('slug', $slug)
            ->with(['category', 'author', 'tags', 'keywords', 'featuredMedia'])
            ->first();
    }

    public function archive(int $perPage = 20): LengthAwarePaginator
    {
        return $this->publishedBase()->latest('published_at')->paginate($perPage);
    }

    protected function publishedBase(): Builder
    {
        $query = Article::query()->published();

        if ($siteId = $this->siteContext->id()) {
            $query->where('site_id', $siteId);
        }

        return $query;
    }
}
