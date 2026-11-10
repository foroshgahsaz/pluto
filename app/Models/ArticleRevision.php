<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleRevision extends Model
{
    use BelongsToSite;

    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'site_id',
        'user_id',
        'version',
        'snapshot_json',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_json' => 'array',
            'created_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
