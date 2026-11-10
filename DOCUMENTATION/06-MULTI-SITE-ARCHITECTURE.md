# 06 — Multi-Site Architecture

## Goal

One Laravel app + one MySQL database + one Filament panel managing many news sites, with **hard logical isolation** by `site_id`.

## URL model (public — future)

| URL | Site |
|-----|------|
| `https://example.com/` | Central site (`type=central`, slug empty or special) |
| `https://example.com/tehran/` | Regional (`slug=tehran`) |
| `https://example.com/fars/` | Regional (`slug=fars`) |

Public route middleware will resolve `Site` by first path segment (reserved segments: `admin`, `livewire`, `storage`, API prefixes if any).

**Admin panel** will use a path such as `/admin` and will **not** rely on regional subdirectories for authorization.

## Isolation layers (defense in depth)

1. **Membership:** user must be linked in `site_user` (Super Admin exempt).
2. **Permission:** Spatie permission / role for the action (optionally team-scoped to site).
3. **Query scope:** `SiteScope` on all `BelongsToSite` models.
4. **Policy:** load target model → compare `model.site_id` to authorized set.
5. **Filament resource query:** `getEloquentQuery()` applies same constraints (belt-and-suspenders).
6. **File access:** download route checks media `site_id` + permission.
7. **Audit:** sensitive cross-site bypasses logged.

Hiding UI is never enough.

## SiteContext service

```text
SiteContext::set(?Site $site)
SiteContext::id(): ?int
SiteContext::clear()
SiteContext::withoutScope(callable): mixed   // only for authorized services
```

Queue jobs receive `site_id` in payload and set context at handle start.

## Creating a new regional site (target UX)

Admin action in Filament:

1. Validate unique slug, name, parent/central relation.
2. Transaction: create `sites` row → default categories/menus/settings/homepage section stubs → optional admin assignment.
3. Activity log: `site.created`.
4. No new Composer project, no new deployable unit.

## Deactivation

`is_active=false` blocks public resolution (future) and may block non–Super Admin writes. Data retained.

## Explicit scope bypass policy

Allowed only in:

- Super Admin cross-site reports
- System maintenance commands
- Backup/restore services

Each bypass:

- Requires authorization check
- Uses `SiteContext::withoutScope` or `Model::withoutGlobalScopes()`
- Must be covered by security tests
- Should emit audit events when touching production data
