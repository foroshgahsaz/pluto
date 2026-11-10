# 09 — Security Checklist

Evidence primarily from Stage 2–8 automated tests (`php artisan test` — 63 passed) and Stage 8 hardening. Marked items have code + tests unless noted as ops.

## Tenant isolation
- [x] Global scope applied to all site-scoped models
- [x] Policies deny cross-site view/update/delete
- [x] Filament resources cannot list other sites’ records (authorized site IDs)
- [x] Direct HTTP IDOR attempts fail (404/403) — private media / backups
- [x] Private media download denied cross-site
- [x] Form submissions isolated
- [x] Export endpoints respect site scope
- [x] Scope bypasses limited, authorized, audited, tested

## AuthN / AuthZ
- [x] OTP request/verify rate limited / attempt capped
- [x] Failed OTP and login events recorded
- [x] OTP stored hashed; testing code only via config
- [x] Inactive/blocked users cannot access panel
- [x] CSRF enabled (Laravel web middleware)
- [x] Session fixation protections active (Laravel defaults)
- [x] Policies cover resources through Stage 8
- [x] Regional admin cannot assign super_admin
- [x] Publish permission enforced

## Data & files
- [x] Validation on writes (forms/media/editorial)
- [x] Mass assignment locked down (`$fillable`)
- [x] Upload MIME/size checks
- [x] Dangerous extensions blocked
- [x] Private disk used for private files + backups
- [x] No secrets in logs or repo (`.env.example` placeholders only)

## Ops
- [x] APP_DEBUG false required in production docs
- [x] HTTPS enforced via `FORCE_HTTPS` / production default + `ForceHttps` middleware
- [x] Secure cookies documented (`SESSION_SECURE_COOKIE`)
- [x] Backups on private disk; optional archive password; Super Admin download only
- [ ] Restore procedure tested on non-prod — **ops drill required** (`pluto:restore-guide`)
- [x] `composer audit` clean (2026-11-10: no advisories)

## Mandatory isolation tests (must pass)
1. Tehran user cannot view Fars articles — covered
2. Tehran user cannot edit Fars articles — covered
3. Tehran user cannot delete Fars articles — covered
4. Tehran user cannot view Fars form submissions — covered
5. Tehran user cannot download Fars private files — covered
6. Changing URL/ID cannot access other site data — covered
7. User without publish permission cannot publish — covered
8. Deactivated user cannot access admin — covered
9. Regional admin cannot self-assign super_admin — covered
10. Direct HTTP cannot bypass authz/isolation — covered
