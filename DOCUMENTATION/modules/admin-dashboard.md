# Module: admin dashboard

**Status:** Implemented (Stage 14)  
**Related overview docs:** `DOCUMENTATION/14-DASHBOARD-AND-REPORTS.md`

## Related source files
- `App\Filament\Pages\Dashboard` + `resources/views/filament/pages/dashboard.blade.php`
- `public/css/pluto-dashboard.css`
- `App\Services\Dashboard\AdminDashboardService`
- `App\Services\Analytics\RecordPageView`
- `App\Models\SiteDailyMetric`
- Migration: `2026_11_10_200000_add_dashboard_metrics_tables.php`

## Dashboard sections (matched to design mockup)
1. Welcome banner (personalized)
2. News queue (today / tomorrow) with detail modal
3. Headline stats: active sites, users, published articles, views
4. Visits chart (7 / 30 / 365 day range)
5. Provincial sites status
6. System performance donut
7. Recent activities, latest news, quick actions

## Required permissions
Dashboard itself is available to any authenticated panel user; data is scoped to `authorizedSiteIds()`.  
Quick actions / deep links still respect resource policies.

## Testing procedures
`php artisan test --filter=AdminDashboardTest`
