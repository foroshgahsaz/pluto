# Pluto — سامانه خبری پلوتون

Enterprise multi-site news management platform.

**Stack:** PHP 8.4 · Laravel 13 · Filament 5 · MySQL/SQLite · Spatie Permission/Activitylog/Backup

## Current status

**Each stage has its own PR — merge in order 1 → 2 → 3 → 4.**

This branch = **STAGE 4 media** (on top of Stages 1–3).

Also included from earlier stages on parent branches:
- Multi-site tenancy, dual auth, editorial workflow
- Filament Persian admin

Stage 4 adds:
- Media library + folders
- Secure upload / private download
- Orphan purge command

Tests on this branch: `php artisan test` → **33 passed**

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
