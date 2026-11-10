<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\PublicSite\PublicSitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        protected PublicSitemapBuilder $builder,
    ) {}

    public function site(Site $site): Response
    {
        $urls = $this->builder->urlsFor($site);

        $xml = view('public.feeds.sitemap', [
            'urls' => $urls,
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    public function index(): Response
    {
        $sites = Site::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'updated_at']);

        $xml = view('public.feeds.sitemap-index', [
            'sites' => $sites,
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /livewire',
            '',
            'Sitemap: '.route('public.sitemap.index'),
        ];

        foreach (Site::query()->where('is_active', true)->orderBy('slug')->pluck('slug') as $slug) {
            $lines[] = 'Sitemap: '.route('public.sitemap', ['site' => $slug]);
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }
}
