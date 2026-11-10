# 22 — Coding Standards

## PHP / Laravel
- Follow PSR-12 + Laravel Pint defaults
- Prefer explicit types on new code (PHP 8.4)
- Use Enums for statuses and field types
- Form Requests or validated Filament forms — no unvalidated input in services
- Policies for authorization — no “UI-only” security
- Transactions for multi-model writes
- Eager load to avoid N+1
- No Repository layer by default

## Filament
- Native resources/widgets/actions first
- Authorization via `canViewAny` etc. backed by policies
- Custom Alpine only for reorder/preview interactions

## Naming
- Actions: `PublishArticle`, `AssignUserToSite`
- Scopes/traits: `BelongsToSite`, `SiteScope`
- Permissions: `articles.publish`

## Commits
- Small, focused commits per stage
- Never commit `.env`, secrets, or backup archives
