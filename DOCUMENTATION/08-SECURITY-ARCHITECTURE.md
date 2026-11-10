# 08 — Security Architecture

## Threat model (MVP)

| Threat | Mitigation |
|--------|------------|
| Cross-site data access (IDOR) | Global scopes + policies + membership checks + tests |
| Privilege escalation | Role assignment policies; cannot self-grant super_admin |
| Brute-force login | Rate limiting; lockout/block flags; login_events |
| XSS in admin content | Filament escaping; purify rich text on output later; store carefully |
| SQLi | Eloquent/Query Builder only; no raw user SQL |
| CSRF | Laravel + Filament defaults |
| Malicious uploads | MIME/extension/size validation; private disk; no executable extensions |
| Session theft | Secure/HttpOnly/SameSite cookies; HTTPS in production |
| Secret leakage | `.env` only; never log passwords/tokens |
| Backup exposure | Private remote disk; authorized restore only |

## Authentication

- Laravel session auth for Filament.
- Password hashing via `bcrypt`/`argon2id` (Laravel default).
- Login rate limit (e.g. 5/min/email+IP).
- `is_active` / `is_blocked` enforced on every panel request.
- Password reset via signed Laravel routes (email config required).
- Logout regenerates/invalidates session.

## Authorization

- Gates via Spatie permissions.
- Model Policies for all Filament resources.
- Site membership checks inside policies for site-scoped models.
- Super Admin gate: explicit permission or role check — documented and tested.

## Tenant isolation

See [06-MULTI-SITE-ARCHITECTURE.md](06-MULTI-SITE-ARCHITECTURE.md). Mandatory security tests listed in [18-TESTING-GUIDE.md](18-TESTING-GUIDE.md).

## Input & mass assignment

- `$fillable` / `$guarded` explicit.
- Form Requests or Filament validation rules server-side.
- Enum casting for statuses.
- Conditional form fields still validated server-side when present/required by rules.

## File security

- Allowlist MIME + extension pairs (images, PDF, office docs as approved).
- Reject scripts/binaries.
- Store private files on `private` disk outside web root.
- Authorized download controller verifies authz + site.
- Optional malware scan hook interface (implementation may be no-op until AV service approved).

## Audit logging

Log at minimum:

- Auth success/failure/logout
- User/role/permission changes
- Site create/update/activate
- Article create/update/delete/publish/unpublish
- Media upload/delete/private download
- Form submission export/delete
- Backup/restore operations

Never log passwords, tokens, or raw secret env values.

## Production hardening (deployment stage)

- `APP_DEBUG=false`
- Force HTTPS
- Security headers (via middleware)
- CORS denied by default
- Dependency auditing (`composer audit`)
- Least-privilege DB user

## OWASP Top 10 mapping (summary)

| OWASP | Approach |
|-------|----------|
| A01 Broken Access Control | Policies + site scopes + tests |
| A02 Cryptographic Failures | HTTPS, hashed passwords, secure cookies |
| A03 Injection | Eloquent, validation |
| A04 Insecure Design | Threat model first; staged delivery |
| A05 Security Misconfiguration | Hardened deploy guide |
| A06 Vulnerable Components | Pin versions; audit |
| A07 Auth Failures | Rate limit, session, deactivation |
| A08 Data Integrity Failures | Signed URLs where needed; revision snapshots |
| A09 Logging Failures | activity_log + login_events |
| A10 SSRF | No user-controlled server-side fetch in MVP |
