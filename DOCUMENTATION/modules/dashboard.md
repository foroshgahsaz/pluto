# Module: dashboard

**Status:** Implemented (Stage 7 + Stage 13)  
**Related overview docs:** `DOCUMENTATION/14-DASHBOARD-AND-REPORTS.md`

## Related source files
- `App\Services\Dashboard\EditorialDashboardService`
- `App\Services\Dashboard\ReportsService`
- Widgets: `ArticleStatsOverview`, `PublishVolumeChart`, `RecentEditorialActivity`
- Page: `ReportsOverview` (+ `Filament\Widgets\Reports\*`)

## Required permissions
`reports.view`

## Testing procedures
`php artisan test --filter=DashboardNotificationsTest`  
`php artisan test --filter=AccessReportsNotificationsTest`
