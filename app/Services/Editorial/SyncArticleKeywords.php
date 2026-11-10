<?php

namespace App\Services\Editorial;

use App\Models\Article;
use Illuminate\Support\Facades\DB;

class SyncArticleKeywords
{
    /**
     * @param  list<string>|null  $keywords
     */
    public function handle(Article $article, ?array $keywords): void
    {
        DB::transaction(function () use ($article, $keywords): void {
            $article->keywords()->delete();

            foreach (collect($keywords ?? [])->filter()->unique()->values() as $keyword) {
                $article->keywords()->create([
                    'site_id' => $article->site_id,
                    'keyword' => trim((string) $keyword),
                ]);
            }
        });
    }
}
