# 21 — Developer Guide

## Working rules
1. Read `DOCUMENTATION/` before changing architecture.
2. Never bypass `SiteScope` without an authorized service method.
3. Put workflow logic in Actions/Services, not Filament pages.
4. Every new site-scoped model: trait `BelongsToSite`, Policy, factory, isolation tests, docs update.
5. Update `25-PROJECT-STATUS.md` and module docs in the same PR as the code.

## Local development (planned)
- `.env` with local MySQL/Redis
- `php artisan queue:work`
- `php artisan schedule:work` for scheduling tests
- Pest for tests

## Module map (planned namespaces)
See [03-SYSTEM-ARCHITECTURE.md](03-SYSTEM-ARCHITECTURE.md).

## Adding a regional site in code/tests
Prefer model factories: `Site::factory()->regional()->create(['slug' => 'tehran'])`.

## Documentation sync checklist
When you change schema, permissions, services, or behavior: update the relevant numbered docs + module file + changelog + status.
