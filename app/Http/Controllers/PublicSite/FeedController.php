<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\Editorial\PublicArticleQuery;
use App\Services\PublicSite\PublicSiteViewData;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function __construct(
        protected PublicArticleQuery $articles,
        protected PublicSiteViewData $viewData,
    ) {}

    public function rss(Site $site): Response
    {
        $branding = $this->viewData->branding($site);
        $items = $this->articles->forFeed(50);

        $xml = view('public.feeds.rss', [
            'site' => $site,
            'branding' => $branding,
            'articles' => $items,
            'channelLink' => route('public.home', $site),
            'feedLink' => route('public.rss', $site),
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
