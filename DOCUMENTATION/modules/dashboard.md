# Module: dashboard

**Status:** Implemented (Stage 7)  
**Related overview docs:** `DOCUMENTATION/14-DASHBOARD-AND-REPORTS.md`

## Related source files
- `App\Services\Dashboard\EditorialDashboardService`
- Widgets: `ArticleStatsOverview`, `PublishVolumeChart`, `RecentEditorialActivity`

## Required permissions
`reports.view`

## Testing procedures
`php artisan test --filter=DashboardNotificationsTest`
