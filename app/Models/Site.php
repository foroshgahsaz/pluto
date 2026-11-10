<?php

namespace App\Models;

use App\Enums\SiteType;
use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'slug',
        'domain',
        'locale',
        'timezone',
        'is_active',
        'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'type' => SiteType::class,
            'is_active' => 'boolean',
            'settings_json' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function isCentral(): bool
    {
        return $this->type === SiteType::Central;
    }
}
