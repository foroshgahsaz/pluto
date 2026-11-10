# Module: access / reports / notifications admin UI

**Status:** Implemented (Stage 13)  
**Related overview docs:** `07-USER-ROLES-AND-PERMISSIONS.md`, `14-DASHBOARD-AND-REPORTS.md`, `15-NOTIFICATIONS.md`

## Related source files
- `App\Services\Access\SyncUserSiteRoles`
- `App\Services\Access\AssignSiteRole`
- `App\Services\Dashboard\ReportsService`
- `App\Services\Notifications\BroadcastSiteNotification`
- Filament: `UserForm` site_roles repeater, `RoleResource`, `ReportsOverview`, `ManageNotifications`
- Widgets: `Filament\Widgets\Reports\*`

## Required permissions
- `users.assign_roles` — edit per-site roles on User CRUD
- `roles.view` / `roles.update` — Role templates UI (Super Admin / regional_admin)
- `reports.view` — Reports page + dashboard widgets
- `notifications.manage` — notifications inbox management + broadcast

## Testing procedures
`php artisan test --filter=AccessReportsNotificationsTest`

## Security considerations
- Role templates (`team_id` null) sync permissions out to site-scoped role copies with the same name
- Broadcast recipients limited to active users of the selected site (+ Super Admins)
- Reports aggregates always pass through `authorizedSiteIds()`
