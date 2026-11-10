<?php

namespace App\Support\Tenancy;

use App\Models\Site;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSite
{
    public static function bootBelongsToSite(): void
    {
        static::addGlobalScope(new SiteScope);

        static::creating(function ($model): void {
            if (! empty($model->site_id)) {
                return;
            }

            $siteId = app(SiteContext::class)->id();

            if ($siteId !== null) {
                $model->site_id = $siteId;
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
