# Module: Media

**Status:** Implemented · Tested

## Module purpose
Secure multi-site media library for Pluto admin.

## Related source files
See `DOCUMENTATION/11-MEDIA-MANAGEMENT.md`.

## Testing procedures
```bash
php artisan test --filter=MediaSecurityTest
```

## Extending
Bind a real `MalwareScanner` implementation in `AppServiceProvider` for production AV scanning.
