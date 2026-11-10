<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\Editorial\PublicArticleQuery;
use App\Services\PublicSite\PublicSiteViewData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(
        protected PublicArticleQuery $articles,
        protected PublicSiteViewData $viewData,
    ) {}

    public function index(Site $site): View
    {
        return view('public.articles.index', [
            'site' => $site,
            'branding' => $this->viewData->branding($site),
            'menu' => $this->viewData->primaryMenu($site),
            'articles' => $this->articles->archive(12),
        ]);
    }

    public function show(Site $site, string $slug): View
    {
        $article = $this->articles->findPublishedBySlug($slug);
        abort_unless($article && (int) $article->site_id === (int) $site->id, 404);

        return view('public.articles.show', [
            'site' => $site,
            'branding' => $this->viewData->branding($site),
            'menu' => $this->viewData->primaryMenu($site),
            'article' => $article,
        ]);
    }

    public function search(Request $request, Site $site): View
    {
        $term = trim((string) $request->query('q', ''));

        return view('public.articles.search', [
            'site' => $site,
            'branding' => $this->viewData->branding($site),
            'menu' => $this->viewData->primaryMenu($site),
            'term' => $term,
            'articles' => $term === '' ? null : $this->articles->search($term, 12),
        ]);
    }
}
