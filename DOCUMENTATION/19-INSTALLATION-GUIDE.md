# 19 — Installation Guide

**Status:** Application scaffolded (Stage 2+) — use steps below.

## Current repository state
Laravel 13 + Filament 5 application through Stage 8 (admin backend complete).

## Prerequisites
- PHP 8.4+ with extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` or `imagick`, `zip`
- Composer 2.x
- MySQL 8.0+ (SQLite OK for local/tests)
- Node.js 20+ / npm (if compiling assets)
- Redis recommended for queues/cache in non-local

## Install steps
1. Clone repository
2. `composer install`
3. Copy `.env.example` → `.env` and set DB / mail / filesystem / OTP / backup vars
4. `php artisan key:generate`
5. Create database
6. `php artisan migrate --seed`
7. `php artisan storage:link` (public disk only)
8. `npm install && npm run build` (if required)
9. Configure queue worker + cron `schedule:run`
10. Access `/admin` with seeded Super Admin; **change password immediately**

## Verify
```bash
php artisan test
composer audit
php artisan pluto:restore-guide
```
