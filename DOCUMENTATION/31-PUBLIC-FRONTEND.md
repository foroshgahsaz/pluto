# 31 — Public Frontend

**Status:** Implemented — Stage 9

## URL model
| URL | Behavior |
|-----|----------|
| `/` | Active **central** site homepage |
| `/{site}` | Active site homepage by slug |
| `/{site}/news` | Published article archive |
| `/{site}/news/{slug}` | Article detail |
| `/{site}/category/{slug}` | Category archive |
| `/{site}/page/{slug}` | Published static page |
| `/{site}/search?q=` | Site-scoped search |
| `/{site}/forms/{slug}` | Public forms (Stage 10) |

Reserved first segments: `admin`, `livewire`, `storage`, `up`, `filament`.

## Resolution
`ResolvePublicSite` middleware loads active `Site` by slug, sets `SiteContext`, then `PublicArticleQuery` / menus / settings read only that site.

## Rendering
Blade RTL theme (`resources/views/public/*`) driven by:
- `SiteSettingsManager` branding/colors/SEO
- Primary `menus` + items
- Active `homepage_sections` (latest/featured/selected/category)

No Filament UI is exposed on public routes.

## Isolation rules
- Draft/scheduled articles never rendered
- Cross-site article/page/category slugs 404
- Inactive sites 404
