# راهنمای نگهداری و عملیات

مخاطب: DevOps / سوپرادمین فنی

## Scheduler و Queue
```bash
* * * * * php artisan schedule:run
php artisan queue:work --tries=3
```
شامل: انتشار زمان‌بندی، بکاپ روزانه، پاکسازی media orphans، مانیتور بکاپ.

## بکاپ / بازیابی
```bash
php artisan backup:run
php artisan pluto:restore-guide
php artisan pluto:restore /path/to/backup.zip --dry-run
```
برای اعمال واقعی فقط روی staging با `--force` و پس از `artisan down`.

## امنیت production
- `APP_DEBUG=false` ، `FORCE_HTTPS=true` ، `SESSION_SECURE_COOKIE=true`
- `OTP_SMS_DRIVER=kavenegar` + کلید API
- `BACKUP_DISKS=local,s3` برای کپی خارج از سرور
- `MALWARE_SCANNER=clamav` در صورت نصب clamscan

## سلامت
- `GET /up`
- `php artisan test`
- `composer audit`
