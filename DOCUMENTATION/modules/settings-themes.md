# Module: settings-themes

**Status:** Implemented (Stage 6)  
**Related overview docs:** `DOCUMENTATION/13-SITE-SETTINGS-AND-THEMES.md`

## Module purpose
Backend storage and Filament management for themes, per-site settings, menus, homepage sections, and static pages.

## Related source files
- Models: `Theme`, `SiteSetting`, `Menu`, `MenuItem`, `HomepageSection`, `Page`
- Service: `App\Services\Settings\SiteSettingsManager`
- Filament: `ThemeResource`, `SiteSettingResource`, `MenuResource` (+ items relation), `HomepageSectionResource`, `PageResource`
- Policies: Theme / SiteSetting / Menu / HomepageSection / Page
- Migration: `2026_11_10_170000_create_themes_settings_menus_tables.php`

## Required permissions
`settings.manage`, `themes.manage`, `menus.manage`, `pages.manage`

## Testing procedures
`php artisan test --filter=SiteSettingsThemesTest`

## Security considerations
Tenant isolation via `site_id` + policies + Filament query scoping. Themes are global; only Super Admin mutates the catalog.
