<?php

namespace App\Services\Analytics;

use App\Models\Article;
use App\Models\Site;
use App\Models\SiteDailyMetric;

class RecordPageView
{
    public function forArticle(Article $article): void
    {
        Article::query()->whereKey($article->id)->increment('view_count');
        $this->incrementSiteDay((int) $article->site_id);
    }

    public function forHomepage(Site $site): void
    {
        $this->incrementSiteDay((int) $site->id);
    }

    protected function incrementSiteDay(int $siteId, int $amount = 1): void
    {
        $today = now()->toDateString();

        $affected = SiteDailyMetric::query()
            ->where('site_id', $siteId)
            ->whereDate('metric_date', $today)
            ->increment('page_views', $amount);

        if ($affected === 0) {
            try {
                SiteDailyMetric::query()->create([
                    'site_id' => $siteId,
                    'metric_date' => $today,
                    'page_views' => $amount,
                ]);
            } catch (\Throwable) {
                SiteDailyMetric::query()
                    ->where('site_id', $siteId)
                    ->whereDate('metric_date', $today)
                    ->increment('page_views', $amount);
            }
        }
    }
}
