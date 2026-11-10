<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\PublicSite\PublicSiteViewData;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected PublicSiteViewData $viewData,
    ) {}

    public function show(Site $site, string $slug): View
    {
        $page = $this->viewData->findPublishedPage($site, $slug);
        abort_unless($page, 404);

        return view('public.pages.show', [
            'site' => $site,
            'branding' => $this->viewData->branding($site),
            'menu' => $this->viewData->primaryMenu($site),
            'page' => $page,
        ]);
    }
}
