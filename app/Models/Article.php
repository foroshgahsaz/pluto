<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Support\Tenancy\BelongsToSite;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use BelongsToSite, HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'category_id',
        'author_id',
        'editor_id',
        'created_by',
        'updated_by',
        'published_by',
        'featured_media_id',
        'title',
        'slug',
        'summary',
        'body',
        'status',
        'is_featured',
        'is_selected',
        'scheduled_for',
        'published_at',
        'meta_title',
        'meta_description',
        'rejection_reason',
        'revision_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'is_featured' => 'boolean',
            'is_selected' => 'boolean',
            'scheduled_for' => 'datetime',
            'published_at' => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(ArticleKeyword::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class)->orderByDesc('version');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ArticleAttachment::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDueForPublish(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::Scheduled)
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now());
    }

    public function isEditableContent(): bool
    {
        return in_array($this->status, [
            ArticleStatus::Draft,
            ArticleStatus::Rejected,
            ArticleStatus::Approved,
            ArticleStatus::Published,
        ], true);
    }
}
