<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Support\Tenancy\SiteContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicSite
{
    public function __construct(
        protected SiteContext $siteContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $site = $request->route('site');

        if ($site instanceof Site) {
            abort_unless($site->is_active, 404);
        } else {
            $site = Site::query()
                ->where('slug', (string) $site)
                ->where('is_active', true)
                ->firstOrFail();

            $request->route()?->setParameter('site', $site);
        }

        $this->siteContext->set($site);

        return $next($request);
    }
}
