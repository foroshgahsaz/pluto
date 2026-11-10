# 24 — Changelog

## 2026-11-10 — STAGE 5 form builder

### Added
- Tables: forms, form_fields, form_submissions, form_submission_files
- Field types: text/textarea/number/date/select/multiselect/file/image
- Multi-step field `step`, reorderable fields, conditional visibility JSON
- Server-side submit validation that respects visibility
- Filament Form resource + fields/submissions relation managers + CSV export
- FormBuilderTest (including Tehran cannot view Fars submissions)

## 2026-11-10 — STAGE 4 media management

### Added
- `media_folders` + media `folder_id` / `category` / `alt_text`
- Secure `MediaUploadService` (MIME/extension/size, UUID names, public/private disks)
- Private download route with site authorization
- Filament Media + MediaFolder resources (FA)
- `media:purge-orphans` command + weekly schedule
- `MalwareScanner` contract + null implementation
- `MediaSecurityTest` (cross-site private download denied)

### Process
Stages now ship as **separate stacked PRs** for sequential merge/testing.

## 2026-11-10 — STAGE 3 editorial system

### Added
- Articles workflow: draft → in_review → approved/rejected → scheduled → published → archived
- Models/tables: articles, tags, authors, media (minimal), revisions, keywords, attachments
- `TransitionArticleStatus`, revision recorder, scheduled publish job (every minute)
- Filament FA resources: Articles (workflow actions), Authors, Tags; Categories parent support
- `PublicArticleQuery` for future public pages (no UI)
- Tests: EditorialWorkflowTest, EditorialIsolationTest

### Test evidence
`php artisan test` → 27 passed

## 2026-11-10 — STAGE 2 core infrastructure

### Added
- Laravel 13.40 application scaffold with Filament 5.11 admin panel (Persian brand پلوتون)
- Packages: spatie/laravel-permission (teams), activitylog, backup
- Models/migrations: sites, site_user, users (mobile, flags), otp_challenges, login_events, categories
- Tenancy: SiteContext, SiteScope, BelongsToSite
- Dual admin login: password (mobile/email) + OTP (log SMS driver)
- Filament resources: Sites, Users, Categories
- Seeders: roles/permissions, central site, one Super Admin
- Feature tests: tenant isolation, auth security, seeder bootstrap (18 passing)

### Owner decisions applied
- Local file storage for MVP
- Dual auth OTP + username/password
- Per-site roles (Option A)

## 2026-11-10 — Owner partial approval + OTP requirement

### Confirmed
- Stack: PHP 8.4 + Laravel 13 + Filament 5
- Filament UI: fully Persian (RTL)
- One Super Admin account required
- Admin login must support mobile + OTP for all management levels

### Added / updated
- `29-OWNER-DECISIONS-LOG.md` (plain-language storage + permission explanations)
- `decisions/ADR-005-mobile-otp-admin-login.md`
- Database/security docs updated for `mobile`, `otp_challenges`, OTP controls

### Still blocked for STAGE 2
- Local vs S3 storage confirmation
- Permission Option A vs B
- SMS provider + OTP-only vs emergency password
- Super Admin mobile for seeder

## 2026-11-10 — Stage 1 documentation

### Added
- Full `DOCUMENTATION/` skeleton (00–28 + modules/diagrams/decisions)
- Project inspection report
- Architecture, database design, ERD, multi-site, security, and roadmap docs
- Owner approval request with open architectural decisions

### Not included
- Laravel application scaffold
- Migrations, Filament code, tests execution
