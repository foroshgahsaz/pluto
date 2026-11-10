<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SiteScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(SiteContext::class);

        if ($context->isBypassingScope()) {
            return;
        }

        $siteId = $context->id();

        if ($siteId === null) {
            return;
        }

        $builder->where($model->getTable().'.site_id', $siteId);
    }
}
