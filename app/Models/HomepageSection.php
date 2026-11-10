<?php

namespace App\Models;

use App\Enums\HomepageSectionType;
use App\Support\Tenancy\BelongsToSite;
use Database\Factories\HomepageSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    /** @use HasFactory<HomepageSectionFactory> */
    use BelongsToSite, HasFactory;

    protected $fillable = [
        'site_id',
        'key',
        'title',
        'type',
        'config_json',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => HomepageSectionType::class,
            'config_json' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
