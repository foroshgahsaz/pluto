# 03 — System Architecture

## 1. Recommended stack (as of 2026-11-10)

| Component | Proposed version | Notes |
|-----------|------------------|-------|
| PHP | **8.4** | Laravel 13 needs ≥8.3; Spatie Activitylog v5 needs ≥8.4 |
| Laravel | **^13.0** (framework ~13.40) | Active support; security until 2028-03-17 |
| Filament | **^5.4** (latest 5.11.x) | Laravel 13 support from 5.4+; Livewire 4 |
| Livewire | **^4.0** | Required by Filament 5 |
| Alpine.js | Bundled with Livewire/Filament | Use only for admin UX interactions |
| SortableJS | Via Filament/Alpine where needed | Form/menu/section reordering |
| MySQL | **8.0+** | Single shared database |
| Pest | **^3 or ^4** (compatible with Laravel 13) | Prefer Pest over raw PHPUnit |
| Queue | Redis (prod) / database (local) | Scheduled publish, backups, notifications |
| Cache | Redis (prod) / file (local) | Permission cache, stats |
| Storage | Local for MVP (S3 later optional) | Files on app server; remote disk can be enabled later via `.env` |
| Admin auth | Mobile + OTP | All management roles; Filament session after verify |

### Proposed third-party packages (minimal)

| Package | Purpose | Justification |
|---------|---------|---------------|
| `filament/filament` | Admin panel | Required by scope |
| `spatie/laravel-permission` ^8 | Roles/permissions | Mature, Filament-friendly, teams support |
| `spatie/laravel-activitylog` ^5 | Audit trail | Meets audit requirements with less custom code |
| `spatie/laravel-backup` ^10 | DB/file backups | Scheduling + remote disks |
| `league/flysystem-aws-s3-v3` (optional) | Remote backup/media | Only if S3-compatible storage is approved |

**Rejected for MVP:** Stancl Tenancy (domain/DB-per-tenant model does not match subdirectory + single DB + central admin), multi-tenancy SaaS packages, React/Vue, unnecessary repository layers.

## 2. Architectural style

- **Monolith Laravel application** (correct for MVP; easier security, transactions, Filament).
- **Modular folders by domain**, not microservices.
- **Service / Action classes** for complex workflows (publish, restore backup, submit form).
- **Policies** for authorization; **Form Requests** for HTTP validation.
- **Events/Listeners/Jobs** for side effects (notifications, scheduled publish).
- **No Repository pattern** unless a concrete reuse/test need appears.

### Suggested application layout

```text
app/
  Actions/           # Single-purpose use cases (PublishArticle, AssignUserToSite)
  Domains/           # Optional grouping as modules grow
    Sites/
    Editorial/
    Media/
    Forms/
    Settings/
    Security/
  Enums/
  Filament/
    Resources/
    Pages/
    Widgets/
  Http/
    Middleware/      # ResolveCurrentSite (public later), EnsureUserIsActive
  Models/
  Policies/
  Services/
  Support/
    Tenancy/         # SiteContext, BelongsToSite, SiteScope
```

## 3. Runtime contexts

| Context | Who | Site resolution |
|---------|-----|-----------------|
| Filament Admin | Staff users | Explicit site filter / active site context in panel (not URL subdirectory) |
| Future public web | Visitors | URL prefix → `Site` by `slug` |
| Console / Queue | System | Site passed explicitly into jobs; never infer insecurely |
| Cross-site admin ops | Super Admin only | Must use authorized services that intentionally bypass scopes and are audited |

## 4. Site context mechanism (admin)

1. User authenticates.
2. System loads permitted `site_id`s from `site_user`.
3. Panel sets **current site context** (session/preferenced site, or “all authorized sites” for Super Admin listings).
4. Eloquent Global Scope `SiteScope` filters queries for models using `BelongsToSite`.
5. Policies additionally verify membership + permission for the target record’s `site_id`.

Hiding Filament navigation is **not** security; scopes + policies are.

## 5. Content workflow (summary)

```text
Draft → In Review → Approved → Scheduled → Published → Archived
                 ↘ Rejected → Draft
```

Transitions are Actions + Policy checks + activity logs. Scheduled publish runs via Scheduler dispatching a queued job that re-checks eligibility.

## 6. Extensibility rules

- New regional site = new `sites` row + config/media roots — **no new deployable app**.
- New form field type = register in field type enum + validator + Filament renderer.
- New theme = `themes` row + settings schema; public renderers come later.
- New permission = seeder + policy update + docs update.

## 7. What Stage 1 does **not** include

No migrations executed, no Filament resources coded, no packages installed in this repository yet. This document is the **approved design target** pending owner sign-off.
