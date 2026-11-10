# 15 — Notifications

**Status:** Implemented — Stage 7

## Channels
- Database notifications (Filament bell) for MVP — `AdminPanelProvider::databaseNotifications()`
- Mail optional later (config flag not enabled)

## Service
`App\Services\Notifications\SiteNotificationService`

## Events triggering notifications
| Event | Recipients |
|-------|------------|
| Article → in_review | Users with `articles.approve` on that site (excluding actor) |
| Article → approved / rejected / published | Article creator |
| Site role assigned | Target user |
| Failed logins ≥ threshold | Super Admins |

## Security threshold
Configured in `config/pluto.php`:
- `security.failed_login_threshold` (default 5)
- `security.failed_login_window_minutes` (default 15)
Deduplicated via cache key per mobile/IP window.

## Rules
- Notification payload includes `site_id` / `article_id` when relevant
- Read/unread via Laravel notifications + Filament database notifications UI
