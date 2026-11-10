# 13 — Site Settings and Themes (Design)

**Status:** Designed — Not Started

## Themes
- Global catalog table `themes` with stable `key` and JSON schema for allowed settings.
- Each `sites.theme_id` selects one theme.
- No public Blade theme rendering in this phase — only store configuration.

## Site settings
Key/value JSON grouped by:
- `branding` (logo media ids, site title, tagline)
- `colors` (primary/secondary/accent tokens)
- `seo` (default meta)
- `general` (timezone display overrides, contact, etc.)

## Menus
`menus` + nested `menu_items` with `sort_order`. Alpine/Sortable for reorder in Filament.

## Homepage sections
`homepage_sections` stores section type + config + order for future public homepage. Admin can reorder without building the public UI.

## Alpine usage
Optional live preview of color tokens in admin only; persistence always via Livewire/Filament server actions.
