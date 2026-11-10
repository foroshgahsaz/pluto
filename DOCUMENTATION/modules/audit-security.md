# Module: audit-security

**Status:** Implemented (hardening Stage 8; audit via Spatie activitylog throughout)

## Related source files
- `ForceHttps`, `SecurityHeaders` middleware
- `LoginAuditor` + failed-login threshold notifications
- Activity logs: editorial, media, backup
- Checklist: `DOCUMENTATION/09-SECURITY-CHECKLIST.md`
- Report: `DOCUMENTATION/30-PRODUCTION-READINESS.md`

## Testing procedures
`php artisan test` (full suite) + `composer audit`
