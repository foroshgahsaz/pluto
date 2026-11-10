<?php

namespace App\Services\PublicSite;

use App\Enums\HomepageSectionType;
use App\Enums\MenuItemType;
use App\Enums\PageStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Site;
use App\Services\Editorial\PublicArticleQuery;
use App\Services\Settings\SiteSettingsManager;
use Illuminate\Support\Collection;

class PublicSiteViewData
{
    public function __construct(
        protected SiteSettingsManager $settings,
        protected PublicArticleQuery $articles,
    ) {}

    /**
     * @return array{title: string, tagline: string, primary: string, secondary: string, accent: string, meta_title: string, meta_description: string}
     */
    public function branding(Site $site): array
    {
        $grouped = $this->settings->allGrouped($site);

        $value = function (string $group, string $key, mixed $default = null) use ($grouped): mixed {
            $raw = $grouped[$group][$key] ?? null;
            if (is_array($raw) && array_key_exists('value', $raw)) {
                return $raw['value'];
            }

            return $raw ?? $default;
        };

        return [
            'title' => (string) ($value('branding', 'title', $site->name) ?: $site->name),
            'tagline' => (string) ($value('branding', 'tagline', '') ?: ''),
            'primary' => (string) ($value('colors', 'primary', '#0f766e') ?: '#0f766e'),
            'secondary' => (string) ($value('colors', 'secondary', '#134e4a') ?: '#134e4a'),
            'accent' => (string) ($value('colors', 'accent', '#f59e0b') ?: '#f59e0b'),
            'meta_title' => (string) ($value('seo', 'meta_title', $site->name) ?: $site->name),
            'meta_description' => (string) ($value('seo', 'meta_description', '') ?: ''),
        ];
    }

    /**
     * @return Collection<int, array{title: string, url: string|null, children: list<array{title: string, url: string|null}>}>
     */
    public function primaryMenu(Site $site): Collection
    {
        $menu = Menu::query()
            ->where('site_id', $site->id)
            ->where('location', 'primary')
            ->with(['items' => fn ($q) => $q->where('is_active', true)->with(['children' => fn ($c) => $c->where('is_active', true)])])
            ->first();

        if (! $menu) {
            return collect();
        }

        return $menu->items->map(fn (MenuItem $item) => $this->mapMenuItem($site, $item));
    }

    /**
     * @return list<array{section: HomepageSection, articles: Collection<int, Article>}>
     */
    public function homepageSections(Site $site): array
    {
        $sections = HomepageSection::query()
            ->where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $result = [];
        foreach ($sections as $section) {
            $limit = (int) ($section->config_json['limit'] ?? 10);
            $articles = match ($section->type) {
                HomepageSectionType::Latest => $this->articles->latest($limit),
                HomepageSectionType::Featured => $this->articles->featured($limit),
                HomepageSectionType::Selected => $this->articles->selected($limit),
                HomepageSectionType::Category => $this->articles->byCategory(
                    (int) ($section->config_json['category_id'] ?? 0),
                    $limit,
                )->getCollection(),
                default => collect(),
            };

            $result[] = [
                'section' => $section,
                'articles' => $articles,
            ];
        }

        return $result;
    }

    public function findPublishedPage(Site $site, string $slug): ?Page
    {
        return Page::query()
            ->where('site_id', $site->id)
            ->where('slug', $slug)
            ->where('status', PageStatus::Published)
            ->first();
    }

    public function findActiveCategory(Site $site, string $slug): ?Category
    {
        return Category::query()
            ->where('site_id', $site->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return array{title: string, url: string|null, children: list<array{title: string, url: string|null}>}
     */
    protected function mapMenuItem(Site $site, MenuItem $item): array
    {
        return [
            'title' => $item->title,
            'url' => $this->resolveMenuUrl($site, $item),
            'children' => $item->children
                ->map(fn (MenuItem $child) => [
                    'title' => $child->title,
                    'url' => $this->resolveMenuUrl($site, $child),
                ])
                ->values()
                ->all(),
        ];
    }

    protected function resolveMenuUrl(Site $site, MenuItem $item): ?string
    {
        return match ($item->type) {
            MenuItemType::Url => $item->url,
            MenuItemType::Page => $item->reference_id
                ? (($page = Page::query()->find($item->reference_id))
                    ? route('public.page', ['site' => $site->slug, 'slug' => $page->slug])
                    : null)
                : null,
            MenuItemType::Category => $item->reference_id
                ? (($category = Category::query()->find($item->reference_id))
                    ? route('public.category', ['site' => $site->slug, 'slug' => $category->slug])
                    : null)
                : null,
            MenuItemType::Article => $item->reference_id
                ? (($article = Article::query()->find($item->reference_id))
                    ? route('public.article', ['site' => $site->slug, 'slug' => $article->slug])
                    : null)
                : null,
        };
    }
}
