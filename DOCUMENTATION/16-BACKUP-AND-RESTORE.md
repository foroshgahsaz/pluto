# 16 — Backup and Restore (Design)

**Status:** Designed — Not Started

## Approach
Use `spatie/laravel-backup` for DB + selected files.

## Requirements
- Scheduled backups (daily recommended)
- Store copies on **remote disk** (S3-compatible / other) in addition to local
- Metadata row in `backups` table for admin visibility
- Filament page for Super Admin: list, trigger, download (authorized), restore instructions
- Restore is a **controlled, documented procedure** (preferably CLI in maintenance mode), tested on staging

## Security
- Backup archives must not be publicly URL-accessible
- Restore requires `backups.manage` + Super Admin
- Audit every backup/restore attempt
- Encrypt archives when package/config supports and owner approves passphrase management

## Restore outline (to be verified in STAGE 8)
1. Enable maintenance mode
2. Restore DB from dump
3. Restore files from archive
4. Clear caches
5. Run smoke tests
6. Disable maintenance mode
7. Record activity log
