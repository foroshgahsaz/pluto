<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ArticleStatus;
use App\Filament\Concerns\GuardsAuthorizedSiteId;
use App\Filament\Resources\Articles\ArticleResource;
use App\Services\Editorial\ArticleRevisionRecorder;
use App\Services\Editorial\StoreArticleFeaturedImage;
use App\Services\Editorial\SyncArticleKeywords;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateArticle extends CreateRecord
{
    use GuardsAuthorizedSiteId;

    protected static string $resource = ArticleResource::class;

    /** @var list<string> */
    public array $keyword_list = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->guardSiteId($data, 'articles.create', sanitizeBody: true);
        $data['status'] = ArticleStatus::Draft->value;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        unset($data['featured_image'], $data['keyword_list']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $featuredImage = $this->form->getState()['featured_image'] ?? null;
        $keywords = $this->form->getState()['keyword_list'] ?? $this->keyword_list;

        /** @var \App\Models\Article $article */
        $article = parent::handleRecordCreation($data);

        app(SyncArticleKeywords::class)->handle($article, is_array($keywords) ? $keywords : []);
        app(StoreArticleFeaturedImage::class)->handle($article, is_string($featuredImage) ? $featuredImage : null, auth()->user());
        app(ArticleRevisionRecorder::class)->record($article, auth()->user(), 'ایجاد خبر');

        activity('editorial')
            ->causedBy(auth()->user())
            ->performedOn($article)
            ->withProperties(['site_id' => $article->site_id])
            ->log('article.created');

        return $article->fresh();
    }
}
