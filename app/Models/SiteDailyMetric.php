<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteDailyMetric extends Model
{
    protected $fillable = [
        'site_id',
        'metric_date',
        'page_views',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'page_views' => 'integer',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
