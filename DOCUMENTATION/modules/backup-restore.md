# Backup / restore module

## Commands
- `php artisan pluto:backup database|files|full`
- `php artisan backup:clean` / `backup:monitor`
- `php artisan pluto:restore-guide`
- `php artisan pluto:restore {archive} --dry-run|--force`

## Records
`backup_records`: `status`, `type` (`database|files|full`), disk/path/size, message, triggered_by, finished_at.

## UI
- پشتیبان‌گیری: manual actions + schedule summary + failure banner
- نمایش خطاها: failed backups / failed jobs / app log errors
