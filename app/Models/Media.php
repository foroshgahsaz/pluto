<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToSite;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use BelongsToSite, HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'site_id',
        'folder_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'visibility',
        'category',
        'alt_text',
        'checksum',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function articleAttachments(): HasMany
    {
        return $this->hasMany(ArticleAttachment::class);
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private' || $this->disk === config('pluto.media.private_disk', 'local');
    }

    public function isImage(): bool
    {
        return is_string($this->mime_type) && str_starts_with($this->mime_type, 'image/');
    }

    public function publicUrl(): ?string
    {
        if ($this->isPrivate()) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }
}
