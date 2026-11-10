<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGlobalOperator() || $user->sites()->exists();
    }

    public function view(User $user, Article $article): bool
    {
        return $user->hasSitePermission('articles.view', $article->site_id);
    }

    public function create(User $user): bool
    {
        if ($user->isGlobalOperator()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('articles.create', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, Article $article): bool
    {
        return $user->hasSitePermission('articles.update', $article->site_id);
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasSitePermission('articles.delete', $article->site_id);
    }

    public function submit(User $user, Article $article): bool
    {
        return $this->update($user, $article);
    }

    public function approve(User $user, Article $article): bool
    {
        return $user->hasSitePermission('articles.approve', $article->site_id);
    }

    public function reject(User $user, Article $article): bool
    {
        return $this->approve($user, $article);
    }

    public function publish(User $user, Article $article): bool
    {
        return $user->hasSitePermission('articles.publish', $article->site_id);
    }

    public function schedule(User $user, Article $article): bool
    {
        return $user->hasSitePermission('articles.schedule', $article->site_id);
    }

    public function feature(User $user, Article $article): bool
    {
        return $user->hasSitePermission('articles.feature', $article->site_id);
    }

    public function archive(User $user, Article $article): bool
    {
        return $this->publish($user, $article);
    }
}
