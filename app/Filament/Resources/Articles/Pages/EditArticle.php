<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Services\Editorial\ArticleRevisionRecorder;
use App\Services\Editorial\StoreArticleFeaturedImage;
use App\Services\Editorial\SyncArticleKeywords;
use App\Services\Editorial\TransitionArticleStatus;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Article $record */
        $record = $this->getRecord();
        $data['keyword_list'] = $record->keywords()->pluck('keyword')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        unset($data['featured_image'], $data['keyword_list'], $data['status']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Article $record */
        $featuredImage = $this->form->getState()['featured_image'] ?? null;
        $keywords = $this->form->getState()['keyword_list'] ?? [];

        $record = parent::handleRecordUpdate($record, $data);

        app(SyncArticleKeywords::class)->handle($record, is_array($keywords) ? $keywords : []);
        app(StoreArticleFeaturedImage::class)->handle($record, is_string($featuredImage) ? $featuredImage : null, auth()->user());
        app(ArticleRevisionRecorder::class)->record($record, auth()->user(), 'ویرایش خبر');

        activity('editorial')
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['site_id' => $record->site_id])
            ->log('article.updated');

        return $record->fresh();
    }

    protected function getHeaderActions(): array
    {
        /** @var Article $article */
        $article = $this->getRecord();
        $user = auth()->user();
        $transitions = app(TransitionArticleStatus::class);

        return [
            Action::make('submit')
                ->label('ارسال برای بررسی')
                ->visible(fn () => $article->status === ArticleStatus::Draft && $user?->can('submit', $article))
                ->requiresConfirmation()
                ->action(function () use ($transitions, $article): void {
                    $transitions->submit(auth()->user(), $article);
                    $this->refreshFormData(['status', 'rejection_reason']);
                    Notification::make()->title('خبر برای بررسی ارسال شد')->success()->send();
                }),

            Action::make('approve')
                ->label('تأیید')
                ->color('success')
                ->visible(fn () => $article->status === ArticleStatus::InReview && $user?->can('approve', $article))
                ->requiresConfirmation()
                ->action(function () use ($transitions, $article): void {
                    $transitions->approve(auth()->user(), $article);
                    $this->refreshFormData(['status', 'editor_id', 'rejection_reason']);
                    Notification::make()->title('خبر تأیید شد')->success()->send();
                }),

            Action::make('reject')
                ->label('رد')
                ->color('danger')
                ->visible(fn () => $article->status === ArticleStatus::InReview && $user?->can('reject', $article))
                ->form([
                    Textarea::make('rejection_reason')->label('دلیل رد')->required(),
                ])
                ->action(function (array $data) use ($transitions, $article): void {
                    $transitions->reject(auth()->user(), $article, $data['rejection_reason']);
                    $this->refreshFormData(['status', 'rejection_reason', 'editor_id']);
                    Notification::make()->title('خبر رد شد')->warning()->send();
                }),

            Action::make('returnToDraft')
                ->label('بازگشت به پیش‌نویس')
                ->visible(fn () => $article->status === ArticleStatus::Rejected && $user?->can('update', $article))
                ->action(function () use ($transitions, $article): void {
                    $transitions->returnRejectedToDraft(auth()->user(), $article);
                    $this->refreshFormData(['status', 'rejection_reason']);
                    Notification::make()->title('به پیش‌نویس بازگشت')->success()->send();
                }),

            Action::make('publishNow')
                ->label('انتشار فوری')
                ->color('success')
                ->visible(fn () => in_array($article->status, [ArticleStatus::Approved, ArticleStatus::Scheduled], true) && $user?->can('publish', $article))
                ->requiresConfirmation()
                ->action(function () use ($transitions, $article): void {
                    $transitions->publishNow(auth()->user(), $article);
                    $this->refreshFormData(['status', 'published_at', 'scheduled_for']);
                    Notification::make()->title('خبر منتشر شد')->success()->send();
                }),

            Action::make('schedule')
                ->label('زمان‌بندی انتشار')
                ->visible(fn () => $article->status === ArticleStatus::Approved && $user?->can('schedule', $article))
                ->form([
                    DateTimePicker::make('scheduled_for')->label('زمان انتشار')->required()->seconds(false)->minDate(now()->addMinute()),
                ])
                ->action(function (array $data) use ($transitions, $article): void {
                    $transitions->schedule(auth()->user(), $article, new \DateTimeImmutable($data['scheduled_for']));
                    $this->refreshFormData(['status', 'scheduled_for']);
                    Notification::make()->title('انتشار زمان‌بندی شد')->success()->send();
                }),

            Action::make('cancelSchedule')
                ->label('لغو زمان‌بندی')
                ->visible(fn () => $article->status === ArticleStatus::Scheduled && $user?->can('schedule', $article))
                ->action(function () use ($transitions, $article): void {
                    $transitions->cancelSchedule(auth()->user(), $article);
                    $this->refreshFormData(['status', 'scheduled_for']);
                    Notification::make()->title('زمان‌بندی لغو شد')->success()->send();
                }),

            Action::make('archive')
                ->label('بایگانی')
                ->visible(fn () => $article->status === ArticleStatus::Published && $user?->can('archive', $article))
                ->requiresConfirmation()
                ->action(function () use ($transitions, $article): void {
                    $transitions->archive(auth()->user(), $article);
                    $this->refreshFormData(['status']);
                    Notification::make()->title('خبر بایگانی شد')->success()->send();
                }),

            Action::make('unpublish')
                ->label('لغو انتشار')
                ->color('warning')
                ->visible(fn () => $article->status === ArticleStatus::Published && $user?->can('publish', $article))
                ->requiresConfirmation()
                ->action(function () use ($transitions, $article): void {
                    $transitions->unpublishToDraft(auth()->user(), $article);
                    $this->refreshFormData(['status', 'published_at', 'scheduled_for']);
                    Notification::make()->title('خبر به پیش‌نویس برگشت')->success()->send();
                }),

            DeleteAction::make()
                ->visible(fn () => $user?->can('delete', $article)),
        ];
    }
}
