# 01 — Project Overview

## Product

**Pluto** is a centralized multi-site news management platform for an organization that operates one central news website and multiple regional websites (for example Tehran, Fars, Isfahan).

Administrators manage all sites from **one** Filament administration panel. Regional public sites are served later as subdirectory routes (for example `/tehran/`). Public frontend templates are **out of scope** until backend approval.

## Business goals (MVP)

1. Create and manage central + regional sites without installing new Laravel apps.
2. Isolate each site’s content, media, forms, menus, and settings by `site_id`.
3. Enforce role- and site-based authorization on the server.
4. Support a controlled editorial workflow (draft → review → publish / schedule).
5. Provide media management, form builder backend, theme/settings storage, dashboard stats, notifications, audit logging, and backups.
6. Ship complete technical documentation so the project can be maintained without Cursor history.

## Technical goals

| Priority | Goal |
|----------|------|
| 1 | Security and tenant isolation |
| 2 | Data integrity |
| 3 | Correct architecture |
| 4 | Maintainability |
| 5 | Scalability |
| 6 | Documentation |
| 7 | Development speed |

Security and architecture always beat speed.

## High-level system shape

```text
                    ┌─────────────────────────────┐
                    │   Central Filament Admin     │
                    │   (one panel, all sites)     │
                    └──────────────┬──────────────┘
                                   │
                    ┌──────────────▼──────────────┐
                    │     Shared Laravel App       │
                    │  Services · Policies · Jobs  │
                    └──────────────┬──────────────┘
                                   │
                    ┌──────────────▼──────────────┐
                    │   Single MySQL Database      │
                    │   Logical isolation: site_id │
                    └─────────────────────────────┘

Future public sites (not in Stage 1 implementation):
  example.com/          → central site
  example.com/tehran/   → Tehran site
  example.com/fars/     → Fars site
```

## What exists today

| Item | State |
|------|-------|
| Git repository | Present (`foroshgahsaz/pluto`) |
| Application code | **None** (README only) |
| PHP / Composer in current Cloud Agent image | **Not installed** |
| Documentation (Stage 1) | Created in this PR |

## Proposed product name in code

| Layer | Value |
|-------|-------|
| Repository | `pluto` |
| App name | Pluto |
| Admin panel ID | `admin` |
| Namespace | `App\` (Laravel default) |

Persian UI copy for Filament may be enabled after owner confirmation (see approval request).
