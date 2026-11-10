<?php

namespace App\Services\PublicSite;

use App\Enums\PageStatus;
use App\Models\Category;
use App\Models\Form;
use App\Models\Page;
use App\Models\Site;
use App\Services\Editorial\PublicArticleQuery;
use App\Support\Tenancy\SiteContext;
use Carbon\CarbonInterface;

class PublicSitemapBuilder
{
    public function __construct(
        protected PublicArticleQuery $articles,
        protected SiteContext $siteContext,
    ) {}

    /**
     * @return list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    public function urlsFor(Site $site): array
    {
        $this->siteContext->set($site);
        $urls = [];

        $urls[] = $this->entry(route('public.home', $site), $site->updated_at, 'daily', '1.0');
        $urls[] = $this->entry(route('public.news', $site), $site->updated_at, 'hourly', '0.9');
        $urls[] = $this->entry(route('public.rss', $site), now(), 'hourly', '0.5');

        foreach ($this->articles->forSitemap() as $article) {
            $urls[] = $this->entry(
                route('public.article', [$site, $article->slug]),
                $article->updated_at ?? $article->published_at,
                'weekly',
                '0.8',
            );
        }

        $categories = Category::query()
            ->where('site_id', $site->id)
            ->where('is_active', true)
            ->get(['slug', 'updated_at']);

        foreach ($categories as $category) {
            $urls[] = $this->entry(
                route('public.category', [$site, $category->slug]),
                $category->updated_at,
                'daily',
                '0.6',
            );
        }

        $pages = Page::query()
            ->where('site_id', $site->id)
            ->where('status', PageStatus::Published)
            ->get(['slug', 'updated_at']);

        foreach ($pages as $page) {
            $urls[] = $this->entry(
                route('public.page', [$site, $page->slug]),
                $page->updated_at,
                'monthly',
                '0.5',
            );
        }

        $forms = Form::query()
            ->where('site_id', $site->id)
            ->where('is_active', true)
            ->get(['slug', 'updated_at']);

        foreach ($forms as $form) {
            $urls[] = $this->entry(
                route('public.form', [$site, $form->slug]),
                $form->updated_at,
                'monthly',
                '0.4',
            );
        }

        return $urls;
    }

    /**
     * @return array{loc: string, lastmod: ?string, changefreq: string, priority: string}
     */
    protected function entry(string $loc, mixed $lastmod, string $changefreq, string $priority): array
    {
        $stamp = null;
        if ($lastmod instanceof CarbonInterface) {
            $stamp = $lastmod->toAtomString();
        }

        return [
            'loc' => $loc,
            'lastmod' => $stamp,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
