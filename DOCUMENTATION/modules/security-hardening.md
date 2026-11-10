# Module: security hardening

**Status:** Implemented (Stage 16)  
**Related:** `DOCUMENTATION/08-SECURITY-ARCHITECTURE.md`

## Fixes in this stage
1. **Tenant write isolation** — Filament `site_id` options limited to `authorizedSiteIds()`; create/update guards via `GuardsAuthorizedSiteId`
2. **Zip-slip** — `SafeZipExtractor` used by `pluto:restore`
3. **Stored XSS** — `HtmlSanitizer` (HTMLPurifier allowlist) on article/page save + public render
4. **Featured image** — reprocessed through `MediaUploadService` (MIME/extension/malware checks)
5. **OTP** — production blocks `log` SMS driver; log gateway redacts OTP digits / masks mobile
6. **Headers** — CSP, COOP/CORP, HSTS when HTTPS; secure session cookie defaults with FORCE_HTTPS/production

## Testing
`php artisan test --filter=SecurityHardeningTest`
