# 26 — Next Steps / Development Roadmap

## STAGE 1 — Analysis & Architecture ✅ (this PR)
Inspect repo, propose stack, design DB/ERD/security/modules/docs. **Wait for approval.**

## STAGE 2 — Core infrastructure
1. Prepare PHP 8.4 + Composer + MySQL/Redis in environment
2. `composer create-project laravel/laravel` (or equivalent) into repo root carefully
3. Install Filament 5 + Spatie Permission (+ Activitylog/Backup as approved)
4. Migrations: sites, users (`mobile`), otp_challenges, site_user, permissions
5. Mobile + OTP Filament login (FA/RTL) + SMS gateway adapter (`log` driver for local)
6. SiteContext, BelongsToSite, SiteScope
7. Filament: SiteResource, UserResource, Role management
8. Seed one Super Admin (mobile) + central site
9. Security tests: isolation + OTP auth + deactivated user (as applicable to core)

## STAGE 3 — Editorial system
Categories, tags, authors, articles, workflow actions, revisions, schedule job, Filament resources, tests

## STAGE 4 — Media
Folders, uploads, private download, attachment relations, file security tests

## STAGE 5 — Form builder
Schema, admin builder UX, submissions, export, isolation tests

## STAGE 6 — Settings / themes / menus / homepage sections ✅
Backend storage + Filament management + reorder UX (this PR)

## STAGE 7 — Dashboard + notifications ✅
Widgets, filters, notification events (this PR)

## STAGE 8 — Security hardening, backups, full test pass, deployment docs, production readiness report

---

## Working method
- One stage at a time
- Design → implement → test → document → owner review
- No public frontend until backend approved
- No silent scope expansion
