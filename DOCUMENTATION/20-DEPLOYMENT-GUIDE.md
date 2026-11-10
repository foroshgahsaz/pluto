# 20 — Deployment Guide

**Status:** Implemented guidance — Stage 8

## Principles
- Zero secrets in git
- `APP_ENV=production`, `APP_DEBUG=false`
- `FORCE_HTTPS=true` (or rely on production default)
- `SESSION_SECURE_COOKIE=true` behind HTTPS
- Separate queue workers (`QUEUE_CONNECTION=database|redis`)
- Cron: `* * * * * php artisan schedule:run`
- Remote backups recommended: `BACKUP_DISKS=local,s3`
- Run migrations in release pipeline **after** a backup

## Suggested release steps
1. Put app in maintenance mode (or atomic release directory)
2. Pull/release code
3. `composer install --no-dev --optimize-autoloader`
4. Build frontend assets if needed
5. `php artisan backup:run` (pre-migrate safety, when DB reachable)
6. `php artisan migrate --force`
7. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
8. Restart PHP-FPM / Octane / queues
9. Smoke test admin login + publish path + cross-site isolation
10. Disable maintenance mode

## Post-deploy checks
- `GET /up` health endpoint
- Admin login (password + OTP path)
- Site listing
- Create draft article on one site; confirm second site cannot see it
- Queue processes scheduled publish job
- Backup job: `php artisan backup:run` (or verify scheduled)
- Security headers present (`X-Frame-Options`, `X-Content-Type-Options`)

## Environment checklist
See `.env.example` for `FORCE_HTTPS`, `BACKUP_*`, `FAILED_LOGIN_*`, OTP, and seed Super Admin vars.
