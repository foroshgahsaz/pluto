# Module: Platform & Tenancy

**Status:** Implemented (STAGE 2 core) · Tested

## Module purpose
Provide multi-site foundation: `sites`, site membership, and query isolation via `site_id`.

## Module responsibilities
- Create/manage central and regional sites
- Maintain `site_user` membership
- Provide `SiteContext`, `BelongsToSite`, `SiteScope`
- Enforce site-aware Filament queries for site-scoped resources

## Related source files
- `app/Models/Site.php`
- `app/Support/Tenancy/*`
- `app/Filament/Resources/Sites/*`
- `app/Policies/SitePolicy.php`
- `database/migrations/2026_11_10_130000_create_sites_table.php`
- `database/migrations/2026_11_10_130100_create_site_user_table.php`

## Classes and services
- `App\Support\Tenancy\SiteContext`
- `App\Support\Tenancy\SiteScope`
- `App\Support\Tenancy\BelongsToSite`

## Models / tables
- `sites`, `site_user`

## Required permissions
`sites.view|create|update|delete|activate`

## Testing procedures
`tests/Feature/TenantIsolationTest.php`

## Security considerations
Global scope + policies + membership. Scope bypass only through `SiteContext::withoutScope()` for authorized services.
