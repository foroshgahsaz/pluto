# Pluto — سامانه خبری پلوتون

Enterprise multi-site news management platform.

**Stack:** PHP 8.4 · Laravel 13 · Filament 5 · MySQL/SQLite · Spatie Permission/Activitylog/Backup

## Current status

**STAGE 3 editorial system implemented on this branch** (after Stage 1 docs + Stage 2 core).

Implemented:
- Multi-site (`sites`, `site_user`) with `SiteScope`
- Users with dual login: **password** (mobile/email) + **OTP**
- Per-site roles (Spatie teams)
- Filament admin (Persian): Sites / Users / Categories / Articles / Authors / Tags
- Editorial workflow, revisions, scheduled publishing
- Security + editorial feature tests (`php artisan test` → 27 passed)

## Docs

Start at [`DOCUMENTATION/00-README.md`](DOCUMENTATION/00-README.md).  
Owner decisions: [`DOCUMENTATION/29-OWNER-DECISIONS-LOG.md`](DOCUMENTATION/29-OWNER-DECISIONS-LOG.md).

## Local setup (dev)

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # or configure MySQL
php artisan migrate --seed
php artisan serve
```

Admin panel: `/admin`

Default seeded Super Admin (change immediately):

- Mobile: `09120000000` (or `SEED_SUPER_ADMIN_MOBILE`)
- Email: `admin@pluto.test`
- Password: `ChangeMe!23456` (or `SEED_SUPER_ADMIN_PASSWORD`)

OTP in local/test uses `OTP_SMS_DRIVER=log` and testing code `123456`.

## Tests

```bash
php artisan test
```
