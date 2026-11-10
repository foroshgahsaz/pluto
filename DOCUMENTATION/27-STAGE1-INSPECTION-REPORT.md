# 27 — Stage 1 Inspection Report

**Date:** 2026-11-10  
**Repository:** `https://github.com/foroshgahsaz/pluto`  
**Branch inspected:** `main` → work branch `cursor/stage1-architecture-docs-4d4c`

## 1. What exists in the repository

| Path | Finding |
|------|---------|
| `README.md` | Title “pluto” / سامانه خبری پلوتون only |
| Application code | **None** |
| `composer.json` | **Missing** |
| `artisan` | **Missing** |
| Tests | **Missing** |
| CI config | **Not present** in tree |
| Documentation | Created in Stage 1 |

`git log` shows a single initial commit.

## 2. Runtime environment (Cloud Agent)

| Tool | Status |
|------|--------|
| PHP | Not installed (`php: command not found`) |
| Composer | Not installed |
| Node/npm | Present (Node via nvm / exec-daemon) |
| MySQL | Not verified / not required for Stage 1 docs |

**Risk:** STAGE 2 cannot run `composer create-project` or tests until PHP 8.4+ and Composer are provisioned in the environment.

## 3. Compatibility research (2026-11-10)

| Package | Latest relevant | Recommendation |
|---------|-----------------|----------------|
| Laravel framework | 13.40.0 | Adopt Laravel **13** |
| PHP for Laravel 13 | 8.3–8.5 | Prefer **8.4** |
| Filament | 5.11.2 | Adopt Filament **^5.4** (5.x) |
| Livewire | 4.x (via Filament 5) | Accept Livewire 4 |
| spatie/laravel-permission | 8.5.0 | Adopt ^8 (Laravel 12/13) |
| spatie/laravel-activitylog | 5.1.4 | Needs PHP **^8.4** |
| spatie/laravel-backup | 10.3.x / 10.4.x | Adopt ^10; pin after install |

Laravel 12 is security-only (bug fixes ended Aug 2026). New greenfield project should use Laravel 13.

## 4. Architectural risks identified early

1. **Empty repo + missing PHP toolchain** — environment setup is part of STAGE 2 critical path.
2. **Multi-site IDOR** — highest business risk; requires scopes + policies + mandatory tests from day one of data models.
3. **Permission model complexity** — site-scoped roles (Spatie teams) vs global roles; owner must choose.
4. **Public frontend temptation** — explicitly deferred; keep Filament-only until approval.
5. **Backup restore danger** — must be Super-Admin-only and tested on non-production.
6. **Rich-text XSS** — editorial HTML must have a sanitization strategy before public render (even if public UI is later).
7. **Scheduler dependence** — scheduled publishing needs supervised queue workers in production.

## 5. Module breakdown (implementation units)

1. Platform & Tenancy  
2. Identity & Access  
3. Editorial  
4. Media  
5. Forms  
6. Settings/Themes/Menus  
7. Dashboard/Reports  
8. Notifications  
9. Audit/Security Ops  
10. Backup/Restore  
11. Documentation & QA (continuous)

## 6. Conclusion

The project is a **greenfield** build named Pluto. Stage 1 delivers architecture and documentation only. **No module implementation has started.** Proceed to STAGE 2 only after owner approval of decisions in `28-APPROVAL-REQUEST.md`.
