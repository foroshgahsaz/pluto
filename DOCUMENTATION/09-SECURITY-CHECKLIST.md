# 09 — Security Checklist

Use this before declaring any stage production-ready. Mark only after evidence.

## Tenant isolation
- [ ] Global scope applied to all site-scoped models
- [ ] Policies deny cross-site view/update/delete
- [ ] Filament resources cannot list other sites’ records
- [ ] Direct HTTP IDOR attempts fail (404/403)
- [ ] Private media download denied cross-site
- [ ] Form submissions isolated
- [ ] Export endpoints respect site scope
- [ ] Scope bypasses limited, authorized, audited, tested

## AuthN / AuthZ
- [ ] Login rate limited
- [ ] Failed logins recorded
- [ ] Inactive/blocked users cannot access panel
- [ ] CSRF enabled
- [ ] Session fixation protections active
- [ ] Password reset secure
- [ ] Policies cover all resources
- [ ] Regional admin cannot assign super_admin
- [ ] Publish permission enforced

## Data & files
- [ ] Validation on all writes
- [ ] Mass assignment locked down
- [ ] Upload MIME/size checks
- [ ] Dangerous extensions blocked
- [ ] Private disk used for private files
- [ ] No secrets in logs or repo

## Ops
- [ ] APP_DEBUG false in production
- [ ] HTTPS enforced
- [ ] Secure cookies configured
- [ ] Backups encrypted/restricted as designed
- [ ] Restore procedure tested on non-prod
- [ ] `composer audit` clean or risks accepted in writing

## Mandatory isolation tests (must pass)
1. Tehran user cannot view Fars articles
2. Tehran user cannot edit Fars articles
3. Tehran user cannot delete Fars articles
4. Tehran user cannot view Fars form submissions
5. Tehran user cannot download Fars private files
6. Changing URL/ID cannot access other site data
7. User without publish permission cannot publish
8. Deactivated user cannot access admin
9. Regional admin cannot self-assign super_admin
10. Direct HTTP cannot bypass authz/isolation
