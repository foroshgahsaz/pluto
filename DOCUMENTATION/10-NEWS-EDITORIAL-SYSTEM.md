# 10 — News Editorial System (Design)

**Status:** Designed — Not Started (implementation)

## Purpose
Manage news articles per site with controlled workflow, revisions, taxonomy, and scheduling.

## Workflow states

| Status | Meaning | Who typically acts |
|--------|---------|-------------------|
| `draft` | Work in progress | Journalist / editors |
| `in_review` | Submitted for review | Journalist → editors |
| `rejected` | Returned with reason | Editor → Journalist |
| `approved` | Ready to publish/schedule | Editor-in-Chief |
| `scheduled` | Waiting for `scheduled_for` | System job publishes |
| `published` | Live (public later) | Editors with publish |
| `archived` | Removed from primary listings | Editors |

### Transition rules (enforced in Action classes)

```text
draft → in_review
in_review → approved | rejected
rejected → draft
approved → published | scheduled
scheduled → published | approved (cancel schedule)
published → archived | draft (unpublish as controlled action)
```

Unauthorized transitions throw AuthorizationException / DomainException inside a DB transaction.

## Scheduling reliability

1. On schedule: set `status=scheduled`, store `scheduled_for`.
2. Scheduler runs every minute: dispatch `PublishScheduledArticlesJob`.
3. Job selects due articles `FOR UPDATE` / chunkById, re-validates status, publishes, logs activity.
4. Failed jobs retry with backoff; permanent failures alert Super Admin notification.

## Revisions

On significant update/publish, store `article_revisions.snapshot_json` (title, body, meta, status, relations IDs). Retention policy configurable (e.g. last N versions).

## Site isolation

All editorial tables include `site_id`. Taxonomy cannot be shared across sites in MVP (avoids accidental leakage). Authors may optionally link to a global `users` row but remain site-scoped profiles.

## Filament resources (planned)

- ArticleResource (+ revisions relation, attachments)
- CategoryResource (tree)
- TagResource
- AuthorResource

## Out of scope now
Public article HTML templates and visitor search UI.
