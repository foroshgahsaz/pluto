<?php

namespace App\Models;

use App\Enums\MenuItemType;
use App\Support\Tenancy\BelongsToSite;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use BelongsToSite, HasFactory;

    protected $fillable = [
        'menu_id',
        'site_id',
        'parent_id',
        'title',
        'type',
        'reference_id',
        'url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'reference_id' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }
}
