# 23 — Troubleshooting

## Stage 1 known environment issues

### PHP / Composer missing in Cloud Agent
**Symptom:** `php: command not found`, `composer: command not found`  
**Cause:** Base image has no PHP toolchain yet  
**Fix (STAGE 2):** Add PHP 8.4 + Composer to environment install script / Docker image before scaffolding Laravel

### Empty application tree
**Symptom:** No `artisan`, `app/`, `composer.json`  
**Cause:** Repository only contains README + Stage 1 docs  
**Fix:** Approved STAGE 2 will create Laravel project

## Future common issues (planned entries)
- Queue not running → scheduled articles stay `scheduled`
- Permission cache stale → `php artisan permission:cache-reset`
- Site scope returns empty → check `SiteContext` and `site_user`
- Private media 403 → membership/permission/disk config

Entries will be expanded with real incidents after implementation.
