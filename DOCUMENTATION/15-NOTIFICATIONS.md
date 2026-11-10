# 15 — Notifications (Design)

**Status:** Designed — Not Started

## Channels
- Database notifications (Filament bell) for MVP
- Mail optional later (config flag)

## Events triggering notifications
- Article submitted for review
- Article approved / rejected / published
- User assigned to site / role changed (to affected user)
- Backup success/failure (Super Admin)
- Security: repeated failed logins threshold (Super Admin)

## Rules
- Notifications include `site_id` in data when relevant.
- Recipients filtered by role + site membership.
- Read/unread managed via Laravel notifications API + Filament UI.
