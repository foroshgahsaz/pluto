<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\Editorial\TransitionArticleStatus;
use App\Support\Tenancy\SiteContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishScheduledArticlesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(TransitionArticleStatus $transitions, SiteContext $siteContext): void
    {
        $siteContext->withoutScope(function () use ($transitions): void {
            Article::query()
                ->dueForPublish()
                ->orderBy('id')
                ->chunkById(50, function ($articles) use ($transitions): void {
                    foreach ($articles as $article) {
                        try {
                            $transitions->publishDue($article);
                        } catch (Throwable $e) {
                            Log::error('scheduled_article_publish_failed', [
                                'article_id' => $article->id,
                                'site_id' => $article->site_id,
                                'message' => $e->getMessage(),
                            ]);
                        }
                    }
                });
        });
    }
}
