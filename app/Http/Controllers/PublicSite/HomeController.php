<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\SiteType;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\PublicSite\PublicSiteViewData;
use App\Support\Tenancy\SiteContext;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected PublicSiteViewData $viewData,
        protected SiteContext $siteContext,
    ) {}

    public function central(): View
    {
        $site = Site::query()
            ->where('type', SiteType::Central)
            ->where('is_active', true)
            ->firstOrFail();

        $this->siteContext->set($site);

        return $this->render($site);
    }

    public function show(Site $site): View
    {
        return $this->render($site);
    }

    protected function render(Site $site): View
    {
        $branding = $this->viewData->branding($site);

        return view('public.home', [
            'site' => $site,
            'branding' => $branding,
            'menu' => $this->viewData->primaryMenu($site),
            'sections' => $this->viewData->homepageSections($site),
        ]);
    }
}
