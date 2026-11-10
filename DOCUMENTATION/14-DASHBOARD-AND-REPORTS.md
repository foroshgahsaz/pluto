# 14 — Dashboard and Reports (Design)

**Status:** Designed — Not Started

## Widgets (Filament)
- Total / published / draft article counts
- Per-site breakdown (only authorized sites)
- Recent editorial activity
- Simple charts (Filament chart widgets) for publish volume over date range

## Rules
- All aggregates filter by authorized `site_id`s.
- Date-range filters applied in queries, not only UI.
- No caching of cross-user stats without tagging by user/site set (or short TTL with safe invalidation).
- Super Admin may view global aggregates via authorized report service.
