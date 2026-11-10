<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToSite;
use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use BelongsToSite, HasFactory;

    protected $fillable = [
        'site_id',
        'group',
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
