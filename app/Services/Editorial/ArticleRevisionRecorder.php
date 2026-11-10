<?php

namespace App\Services\Editorial;

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ArticleRevisionRecorder
{
    public function record(Article $article, ?User $user = null, ?string $note = null): ArticleRevision
    {
        return DB::transaction(function () use ($article, $user, $note): ArticleRevision {
            $article->refresh();
            $version = ((int) $article->revision_count) + 1;

            $revision = ArticleRevision::query()->create([
                'article_id' => $article->id,
                'site_id' => $article->site_id,
                'user_id' => $user?->id,
                'version' => $version,
                'note' => $note,
                'created_at' => now(),
                'snapshot_json' => [
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'summary' => $article->summary,
                    'body' => $article->body,
                    'status' => $article->status->value,
                    'category_id' => $article->category_id,
                    'author_id' => $article->author_id,
                    'editor_id' => $article->editor_id,
                    'is_featured' => $article->is_featured,
                    'is_selected' => $article->is_selected,
                    'scheduled_for' => $article->scheduled_for?->toIso8601String(),
                    'published_at' => $article->published_at?->toIso8601String(),
                    'meta_title' => $article->meta_title,
                    'meta_description' => $article->meta_description,
                    'rejection_reason' => $article->rejection_reason,
                    'featured_media_id' => $article->featured_media_id,
                    'tag_ids' => $article->tags()->pluck('tags.id')->all(),
                    'keywords' => $article->keywords()->pluck('keyword')->all(),
                ],
            ]);

            $article->forceFill(['revision_count' => $version])->save();

            return $revision;
        });
    }
}
