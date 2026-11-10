# 16 — Backup and Restore

**Status:** Implemented — Stage 8 + Stage 17 schedule/error UX

## Approach
`spatie/laravel-backup` via `php artisan pluto:backup {database|files|full}`:

- **database** → `--only-db`
- **files** → `--only-files` (`storage/app/public` + `storage/app/private`)
- **full** → DB + files

## Destination
- Default disk: `local` (root = `storage/app/private` — not publicly URL-accessible)
- Optional remote: set `BACKUP_DISKS=local,s3` and configure AWS_* env vars
- Optional archive password: `BACKUP_ARCHIVE_PASSWORD` (enables encryption)
- Filename prefixes: `db-`, `files-`, `full-`

## Schedule (2× daily each)
Configurable via `.env`:

- `BACKUP_DATABASE_TIMES=01:00,13:00` → `pluto:backup database`
- `BACKUP_FILES_TIMES=01:30,13:30` → `pluto:backup files`
- `backup:clean` daily 03:15
- `backup:monitor` daily 04:15

## Admin
- Filament `ManageBackups` (Super Admin): list/filter by type & status, manual DB / files / full run, download, restore guide, recent failure banner
- Filament `SystemErrors` (Super Admin): failed backups, failed queue jobs, recent `laravel.log` ERROR+ lines
- Download route `backups.download` — Super Admin + successful file only
- Metadata table: `backup_records` (+ `type`)
- Spatie success/failure events → `BackupRecorder` + Filament DB notifications to Super Admins

## Restore (controlled CLI)
```bash
php artisan pluto:restore-guide
```

1. `php artisan down`
2. Restore DB dump from archive
3. Extract storage files
4. `php artisan migrate --force`
5. Cache config/routes/views
6. `php artisan queue:restart`
7. Smoke tests
8. `php artisan up`
9. Record activity log

Restore is **not** a one-click Filament action.
