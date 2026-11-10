# 10 — News Editorial System

**Status:** Implemented · Tested (STAGE 3)

## Purpose
Manage news articles per site with controlled workflow, revisions, taxonomy, authors, scheduling, and Filament admin UI.

## Workflow states

| Status | Label | Typical actor |
|--------|-------|---------------|
| `draft` | پیش‌نویس | Journalist / editors |
| `in_review` | در حال بررسی | Journalist → editors |
| `rejected` | رد شده | Editor → Journalist |
| `approved` | تأیید شده | Editor-in-Chief |
| `scheduled` | زمان‌بندی شده | System job publishes |
| `published` | منتشر شده | Editors with publish |
| `archived` | بایگانی | Editors |

### Allowed transitions
```text
draft → in_review
in_review → approved | rejected
rejected → draft
approved → published | scheduled | draft
scheduled → published | approved
published → archived | draft
archived → draft
```

Enforced by `App\Services\Editorial\TransitionArticleStatus` + `ArticlePolicy`.

## Scheduling reliability
- Scheduler: `routes/console.php` runs `PublishScheduledArticlesJob` every minute
- Job uses `lockForUpdate`, retries (3), logs failures
- Requires `php artisan schedule:run` / queue worker in production

## Source files
- Models: `Article`, `Author`, `Tag`, `Media`, `ArticleRevision`, `ArticleKeyword`, `ArticleAttachment`
- Services: `TransitionArticleStatus`, `ArticleRevisionRecorder`, `SyncArticleKeywords`, `StoreArticleFeaturedImage`, `PublicArticleQuery`
- Job: `PublishScheduledArticlesJob`
- Filament: `Articles`, `Authors`, `Tags`, `Categories`
- Enum: `ArticleStatus`

## Database tables
`articles`, `tags`, `authors`, `media`, `article_tag`, `article_keywords`, `article_revisions`, `article_attachments`, `categories`

## Permissions
`articles.view|create|update|delete|publish|approve|schedule|feature`

## Public backend queries (no UI)
`PublicArticleQuery` provides latest/featured/selected/category/search/archive/findBySlug for future public site.

## Tests
- `tests/Feature/EditorialWorkflowTest.php`
- `tests/Feature/EditorialIsolationTest.php`

## Security
Site membership + site-scoped Spatie permissions via `User::hasSitePermission()`. Cross-site transitions denied.
