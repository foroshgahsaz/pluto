<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleKeyword extends Model
{
    use BelongsToSite;

    protected $fillable = [
        'article_id',
        'site_id',
        'keyword',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
