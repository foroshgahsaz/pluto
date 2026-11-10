# 20 — Deployment Guide (Design Outline)

**Status:** Design outline — finalize in STAGE 8 after implementation

## Principles
- Zero secrets in git
- `APP_ENV=production`, `APP_DEBUG=false`
- HTTPS only
- Separate queue workers
- Cron: `* * * * * php artisan schedule:run`
- Remote backups enabled
- Run migrations in release pipeline with backup beforehand

## Suggested release steps
1. Put app in maintenance mode (or use atomic release directory)
2. Pull/release code
3. `composer install --no-dev --optimize-autoloader`
4. Build frontend assets if needed
5. Migrate
6. Cache config/routes/views
7. Restart PHP-FPM / Octane / queues
8. Smoke test admin login + publish path
9. Disable maintenance mode

## Post-deploy checks
- Admin login
- Site listing
- Create draft article on one site
- Confirm second site cannot see it (as non-super user)
- Queue processes scheduled jobs
- Backup job dry-run
