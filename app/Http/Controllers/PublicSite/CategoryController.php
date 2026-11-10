<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\Editorial\PublicArticleQuery;
use App\Services\PublicSite\PublicSiteViewData;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected PublicArticleQuery $articles,
        protected PublicSiteViewData $viewData,
    ) {}

    public function show(Site $site, string $slug): View
    {
        $category = $this->viewData->findActiveCategory($site, $slug);
        abort_unless($category, 404);

        return view('public.categories.show', [
            'site' => $site,
            'branding' => $this->viewData->branding($site),
            'menu' => $this->viewData->primaryMenu($site),
            'category' => $category,
            'articles' => $this->articles->byCategory($category->id, 12),
        ]);
    }
}
