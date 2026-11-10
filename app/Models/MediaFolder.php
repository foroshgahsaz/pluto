<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToSite;
use Database\Factories\MediaFolderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFolder extends Model
{
    /** @use HasFactory<MediaFolderFactory> */
    use BelongsToSite, HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'parent_id',
        'name',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }
}
