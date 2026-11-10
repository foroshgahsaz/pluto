# Module: notifications

**Status:** Implemented (Stage 7)  
**Related overview docs:** `DOCUMENTATION/15-NOTIFICATIONS.md`

## Related source files
- Migration: `2026_11_10_180000_create_notifications_table.php`
- `App\Services\Notifications\SiteNotificationService`
- Hooks in `TransitionArticleStatus`, `AssignSiteRole`, `LoginAuditor`
- Filament: `databaseNotifications()` on admin panel

## Required permissions
Recipients resolved by site permissions / Super Admin flags (`notifications.manage` reserved for future admin tools).

## Testing procedures
`php artisan test --filter=DashboardNotificationsTest`

## Security considerations
Failed-login alerts are Super Admin only; editorial alerts stay within site permission boundaries.
