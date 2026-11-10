# 34 — Master Prompt Gap Closure (Stage 12)

Closed remaining implementable items from the original master prompt.

## Delivered here
- Operator guides: `DOCUMENTATION/guides/*`
- Logout audit → `login_events`
- Activity Log Filament resource + login activity widget
- Role/settings activity logging
- `pluto:restore {archive} --dry-run|--force`
- Multiple seeded themes (`default`, `ink-daily`, `field-report`)
- Filament password reset routes
- SMS drivers: `log` | `kavenegar` | `ghasedak`
- Malware drivers: `null` | `clamav`
- Performance smoke budget on public news listing
- `MasterPromptGapsTest`

## Still requires owner/ops (cannot code alone)
- Real SMS API keys / production provider account
- ClamAV binary on host
- Remote S3 backup credentials + staging restore drill
- Merge PR stack + rotate seeded Super Admin password
