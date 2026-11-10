<?php

namespace App\Services\Editorial;

use App\Enums\ArticleStatus;
use App\Exceptions\InvalidArticleTransitionException;
use App\Models\Article;
use App\Models\User;
use App\Services\Notifications\SiteNotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TransitionArticleStatus
{
    public function __construct(
        protected ArticleRevisionRecorder $revisions,
        protected SiteNotificationService $notifications,
    ) {}

    public function submit(User $actor, Article $article): Article
    {
        $this->authorize($actor, $article, 'submit');

        return $this->transition($actor, $article, ArticleStatus::InReview, note: 'ارسال برای بررسی');
    }

    public function approve(User $actor, Article $article): Article
    {
        $this->authorize($actor, $article, 'approve');

        return $this->transition($actor, $article, ArticleStatus::Approved, attributes: [
            'rejection_reason' => null,
            'editor_id' => $actor->id,
        ], note: 'تأیید تحریریه');
    }

    public function reject(User $actor, Article $article, string $reason): Article
    {
        $this->authorize($actor, $article, 'reject');

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'rejection_reason' => 'دلیل رد الزامی است.',
            ]);
        }

        return $this->transition($actor, $article, ArticleStatus::Rejected, attributes: [
            'rejection_reason' => $reason,
            'editor_id' => $actor->id,
        ], note: 'رد خبر');
    }

    public function publishNow(User $actor, Article $article): Article
    {
        $this->authorize($actor, $article, 'publish');

        return $this->transition($actor, $article, ArticleStatus::Published, attributes: [
            'published_at' => now(),
            'published_by' => $actor->id,
            'scheduled_for' => null,
            'rejection_reason' => null,
        ], note: 'انتشار فوری');
    }

    public function schedule(User $actor, Article $article, \DateTimeInterface $when): Article
    {
        $this->authorize($actor, $article, 'schedule');

        if ($when <= now()) {
            throw ValidationException::withMessages([
                'scheduled_for' => 'زمان انتشار باید در آینده باشد.',
            ]);
        }

        return $this->transition($actor, $article, ArticleStatus::Scheduled, attributes: [
            'scheduled_for' => $when,
            'published_at' => null,
            'published_by' => null,
        ], note: 'زمان‌بندی انتشار');
    }

    public function cancelSchedule(User $actor, Article $article): Article
    {
        $this->authorize($actor, $article, 'schedule');

        return $this->transition($actor, $article, ArticleStatus::Approved, attributes: [
            'scheduled_for' => null,
        ], note: 'لغو زمان‌بندی');
    }

    public function unpublishToDraft(User $actor, Article $article): Article
    {
        $this->authorize($actor, $article, 'publish');

        return $this->transition($actor, $article, ArticleStatus::Draft, attributes: [
            'published_at' => null,
            'published_by' => null,
            'scheduled_for' => null,
        ], note: 'بازگشت به پیش‌نویس');
    }

    public function archive(User $actor, Article $article): Article
    {
        $this->authorize($actor, $article, 'archive');

        return $this->transition($actor, $article, ArticleStatus::Archived, note: 'بایگانی');
    }

    public function returnRejectedToDraft(User $actor, Article $article): Article
    {
        $this->authorize($actor, $article, 'update');

        return $this->transition($actor, $article, ArticleStatus::Draft, attributes: [
            'rejection_reason' => null,
        ], note: 'بازگشت از رد شده به پیش‌نویس');
    }

    /**
     * System publish for due scheduled articles (queue/scheduler).
     */
    public function publishDue(Article $article, ?User $systemActor = null): Article
    {
        if ($article->status !== ArticleStatus::Scheduled) {
            throw InvalidArticleTransitionException::make($article->status, ArticleStatus::Published);
        }

        return DB::transaction(function () use ($article, $systemActor): Article {
            /** @var Article $locked */
            $locked = Article::query()->whereKey($article->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ArticleStatus::Scheduled || $locked->scheduled_for === null || $locked->scheduled_for->isFuture()) {
                return $locked;
            }

            $locked->fill([
                'status' => ArticleStatus::Published,
                'published_at' => $locked->scheduled_for,
                'published_by' => $systemActor?->id,
                'scheduled_for' => null,
                'updated_by' => $systemActor?->id,
            ]);
            $locked->save();

            $this->revisions->record($locked, $systemActor, 'انتشار زمان‌بندی‌شده توسط سیستم');

            activity('editorial')
                ->causedBy($systemActor)
                ->performedOn($locked)
                ->withProperties(['site_id' => $locked->site_id, 'event' => 'scheduled_publish'])
                ->log('article.published');

            $published = $locked->fresh(['creator', 'site']);
            $actor = $systemActor ?? new User(['name' => 'سیستم']);
            $this->notifications->notifyArticleTransition(
                $actor,
                $published,
                ArticleStatus::Scheduled,
                ArticleStatus::Published,
            );

            return $published;
        });
    }

    protected function authorize(User $actor, Article $article, string $ability): void
    {
        if (! Gate::forUser($actor)->allows($ability, $article)) {
            throw new AuthorizationException('اجازه این عملیات روی خبر را ندارید.');
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function transition(
        User $actor,
        Article $article,
        ArticleStatus $to,
        array $attributes = [],
        ?string $note = null,
    ): Article {
        return DB::transaction(function () use ($actor, $article, $to, $attributes, $note): Article {
            /** @var Article $locked */
            $locked = Article::query()->whereKey($article->id)->lockForUpdate()->firstOrFail();

            if (! $locked->status->canTransitionTo($to)) {
                throw InvalidArticleTransitionException::make($locked->status, $to);
            }

            if (! $actor->belongsToSite($locked->site_id) && ! $actor->isSuperAdmin()) {
                throw new AuthorizationException('دسترسی به این سایت مجاز نیست.');
            }

            $from = $locked->status;

            $locked->fill(array_merge($attributes, [
                'status' => $to,
                'updated_by' => $actor->id,
            ]));
            $locked->save();

            $this->revisions->record($locked, $actor, $note);

            activity('editorial')
                ->causedBy($actor)
                ->performedOn($locked)
                ->withProperties([
                    'site_id' => $locked->site_id,
                    'from' => $from->value,
                    'to' => $to->value,
                ])
                ->log('article.status_changed');

            $fresh = $locked->fresh(['creator', 'site']);
            $this->notifications->notifyArticleTransition($actor, $fresh, $from, $to);

            return $fresh;
        });
    }
}
