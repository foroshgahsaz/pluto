# 19 — Installation Guide

**Status:** Placeholder until Laravel project is scaffolded in STAGE 2.

## Current repository state
This repo does **not** yet contain a Laravel application. Only documentation (Stage 1) exists.

## Target prerequisites (proposed)
- PHP 8.4+ with extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` or `imagick`, `zip`
- Composer 2.x
- MySQL 8.0+
- Node.js 20+ / npm (for Filament assets if custom theme CSS compiled)
- Redis (recommended for queues/cache in non-local)

## Planned install steps (after STAGE 2 scaffold)
1. Clone repository
2. `composer install`
3. Copy `.env.example` → `.env` and set DB/mail/filesystem
4. `php artisan key:generate`
5. Create database
6. `php artisan migrate --seed`
7. `php artisan storage:link` (for public disk only)
8. `npm install && npm run build` (if required)
9. Configure queue worker + scheduler
10. Access `/admin` with seeded Super Admin credentials from seeder/docs

## Cloud Agent environment note (2026-11-10)
The current Cloud Agent VM image has **no PHP/Composer**. STAGE 2 must add environment setup (Dockerfile / `environment.json` / install script) before code can run tests here.
