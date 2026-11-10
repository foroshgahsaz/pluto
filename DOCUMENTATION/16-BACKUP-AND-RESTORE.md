# 16 — Backup and Restore

**Status:** Implemented — Stage 8

## Approach
`spatie/laravel-backup` backs up MySQL/SQLite dump + `storage/app/public` and `storage/app/private`.

## Destination
- Default disk: `local` (root = `storage/app/private` — not publicly URL-accessible)
- Optional remote: set `BACKUP_DISKS=local,s3` and configure AWS_* env vars
- Optional archive password: `BACKUP_ARCHIVE_PASSWORD` (enables encryption)

## Schedule
- `backup:run` daily 02:15
- `backup:clean` daily 03:15
- `backup:monitor` daily 04:15

## Admin
- Filament page `ManageBackups` (Super Admin only): list `backup_records`, trigger run, download, restore guide modal
- Download route `backups.download` — Super Admin + successful file only
- Metadata table: `backup_records`
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
