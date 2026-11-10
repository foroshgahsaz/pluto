# 14 — Dashboard and Reports

**Status:** Implemented — Stage 7 + Stage 13 + Stage 14

## Command dashboard (Stage 14)
`App\Filament\Pages\Dashboard` mirrors the media command-center mockup:
- welcome, news queue (today/tomorrow + modal), KPI cards, visits chart,
  provincial site status, performance donut, activity feed, latest news, quick actions
- Metrics: `articles.view_count`, `site_daily_metrics`, `RecordPageView` on public home/article
- Styles: `public/css/pluto-dashboard.css` + Vazirmatn

## Widgets (Filament)
- `ArticleStatsOverview` — total / published / draft / in_review / scheduled counts
- `PublishVolumeChart` — publish volume over the last 14 days (line chart)
- `RecentEditorialActivity` — recent Spatie activity log rows (`log_name=editorial`)

## Reports page (Stage 13)
`App\Filament\Pages\ReportsOverview` — filterable by site + date range
- `ReportStatsOverview` — articles / form submissions / media / logins / active users
- `TopCategoriesChart` — top categories bar chart
- `ArticlesBySiteTable` — per-site article totals
- Reuses publish volume + recent editorial activity widgets

## Services
`App\Services\Dashboard\EditorialDashboardService`
- Always scopes aggregates to the viewer’s `authorizedSiteIds()`
- Date-window queries for publish volume applied in SQL, not only the UI

`App\Services\Dashboard\ReportsService`
- Form submissions, media volume, login success/fail, per-site article breakdown, top categories

## Rules
- Widgets / reports page require `reports.view` on at least one authorized site (Super Admin always sees them)
- No cross-user caching of stats
- Super Admin sees global aggregates via the same service (all site IDs)
