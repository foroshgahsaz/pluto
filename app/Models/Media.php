<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToSite;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use BelongsToSite, HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'site_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'visibility',
        'checksum',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): ?string
    {
        if ($this->visibility === 'private') {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
