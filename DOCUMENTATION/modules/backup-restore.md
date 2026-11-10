# Module: backup-restore

**Status:** Implemented (Stage 8)  
**Related overview docs:** `DOCUMENTATION/16-BACKUP-AND-RESTORE.md`

## Related source files
- `config/backup.php`, schedule in `routes/console.php`
- `App\Services\Backup\BackupRecorder`
- Listeners: `RecordSuccessfulBackup`, `RecordFailedBackup`
- Model/table: `BackupRecord` / `backup_records`
- Filament: `ManageBackups`
- Download: `BackupDownloadController`
- CLI: `pluto:restore-guide`

## Required permissions
Super Admin for manage/download/trigger (`backups.manage` seeded for future non-SA tooling).

## Testing procedures
`php artisan test --filter=BackupHardeningTest`
