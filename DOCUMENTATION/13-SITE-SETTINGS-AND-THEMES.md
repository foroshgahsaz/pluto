# 13 — Site Settings and Themes

**Status:** Implemented — Stage 6

## Themes
- Global catalog table `themes` (`key`, `name`, `preview_image`, `is_active`, `config_schema_json`).
- Each site selects one theme via `sites.theme_id`.
- Theme CRUD is Super Admin only; regional admins with `themes.manage` can view the catalog.
- No public Blade theme rendering in this phase — configuration storage only.

## Site settings
Key/value JSON grouped by:
- `branding` (title, tagline, logo refs)
- `colors` (primary/secondary/accent)
- `seo` (default meta)
- `general`

Managed by `App\Services\Settings\SiteSettingsManager` (`get` / `set` / `syncGroup` / `allGrouped`). Unique per `(site_id, group, key)`.

## Menus
`menus` + nested `menu_items` with `sort_order`, `parent_id`, types: url / page / category / article. Filament relation manager supports reorder.

## Homepage sections
`homepage_sections` stores `key`, type (`latest` / `featured` / `selected` / `category` / `custom`), `config_json`, and `sort_order` for the future public homepage. Admin can reorder without building the public UI.

## Pages
Static `pages` with draft/published status, slug uniqueness per site, soft deletes. Content for future public routes only.

## Filament (FA)
Navigation group **ظاهر سایت**: قالب‌ها، تنظیمات سایت، منوها، بخش‌های صفحه اصلی، صفحات ثابت. Site form includes theme select.

## Permissions
`settings.manage`, `themes.manage`, `menus.manage`, `pages.manage` (seeded; granted to `regional_admin`).

## Tests
`tests/Feature/SiteSettingsThemesTest.php` — per-site settings, policy isolation, scope isolation, ordering, theme assignment, seeder defaults.
