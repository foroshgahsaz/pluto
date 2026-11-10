# 14 — Dashboard and Reports

**Status:** Implemented — Stage 7

## Widgets (Filament)
- `ArticleStatsOverview` — total / published / draft / in_review / scheduled counts
- `PublishVolumeChart` — publish volume over the last 14 days (line chart)
- `RecentEditorialActivity` — recent Spatie activity log rows (`log_name=editorial`)

## Service
`App\Services\Dashboard\EditorialDashboardService`
- Always scopes aggregates to the viewer’s `authorizedSiteIds()`
- Date-window queries for publish volume applied in SQL, not only the UI

## Rules
- Widgets require `reports.view` on at least one authorized site (Super Admin always sees them)
- No cross-user caching of stats
- Super Admin sees global aggregates via the same service (all site IDs)
