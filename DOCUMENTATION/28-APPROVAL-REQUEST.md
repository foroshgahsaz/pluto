# 28 — Owner Approval Request (STAGE 1 → STAGE 2 Gate)

Please review the Stage 1 package and answer the decisions below.  
**I will not start Laravel scaffolding or module coding until remaining blockers below are answered.**

> Partial owner reply received 2026-11-10. See [29-OWNER-DECISIONS-LOG.md](29-OWNER-DECISIONS-LOG.md).

---

## A. Findings (short)

1. Repository is empty except README (“pluto” / سامانه خبری پلوتون).
2. No Laravel app exists yet.
3. Current Cloud Agent image has **no PHP/Composer**.
4. Proposed stack is modern and compatible: **PHP 8.4 · Laravel 13 · Filament 5 · MySQL 8 · Pest · Spatie Permission/Activitylog/Backup**.

---

## B. Decisions requiring your approval

### B1. Product naming
- [ ] Confirm product name **Pluto** for app/admin branding  
- [ ] Or specify another English + Persian name

### B2. Stack versions
- [x] Approve **PHP 8.4 + Laravel 13 + Filament 5** ✅ owner confirmed  
- [ ] Or request Laravel 12 (not recommended for new project)

### B3. Admin UI language / direction
- [x] Persian (FA) primary + RTL for Filament ✅ owner: پنل کاملاً فارسی  
- [ ] English primary  
- [ ] Bilingual later (MVP single language: specify which)

### B4. Permission model
Choose one:

- [ ] **Option A (recommended):** Spatie teams enabled — roles can differ per site  
- [ ] **Option B (simpler):** Global roles + `site_user` membership only (same role on all assigned sites)

> Plain explanation: [29-OWNER-DECISIONS-LOG.md](29-OWNER-DECISIONS-LOG.md)

### B5. Packages
- [ ] Approve Spatie Permission + Activitylog + Backup  
- [ ] Request custom audit/backup instead (more code, more maintenance)

### B6. Domain / URL assumptions
- [ ] Confirm subdirectory model: `example.com/{region}/`  
- [ ] Confirm admin at `/admin`  
- [ ] Provide real production domain if known (optional for STAGE 2)

### B7. Central site rule
- [ ] Exactly one `central` site  
- [ ] Allow multiple centrals (not recommended)

### B8. Seeded Super Admin
- [x] Allow a seeder-created Super Admin ✅ owner wants one Super Admin  
- [ ] Provide **mobile number** for seed (required for OTP login). Optional email OK.  
      Do **not** paste production OTP secrets/passwords in chat.

### B9. Storage
- [ ] Local disks only for MVP ← **recommended; awaiting “باشه لوکال”**  
- [ ] S3-compatible remote required from the start for media/backups

> Plain explanation: local = files on same server; S3 = cloud object storage. See decisions log.

### B10. Scope confirmation
- [ ] Confirm: **backend + Filament only** now; **no public frontend**  
- [ ] Confirm: do not add features outside master scope

### B11. Mobile + OTP login (new owner requirement)
- [x] Required for all admin roles ✅  
- [ ] Choose SMS provider: Kavenegar / Ghasedak / other / mock-only for now  
- [ ] OTP-only **or** OTP + emergency password for Super Admin recovery

---

## C. What I will do immediately after approval

1. Create/adjust Cloud environment for PHP 8.4 + Composer + MySQL.  
2. Scaffold Laravel 13 application into this repository.  
3. Install Filament 5 and approved packages.  
4. Implement STAGE 2 only: sites, users/roles, tenancy isolation, seeders, security tests.  
5. Update documentation status from Designed → Implemented/Tested with real evidence.

---

## D. Documents to read for approval

1. [27-STAGE1-INSPECTION-REPORT.md](27-STAGE1-INSPECTION-REPORT.md)  
2. [03-SYSTEM-ARCHITECTURE.md](03-SYSTEM-ARCHITECTURE.md)  
3. [04-DATABASE-DESIGN.md](04-DATABASE-DESIGN.md)  
4. [05-DATABASE-ERD.md](05-DATABASE-ERD.md)  
5. [06-MULTI-SITE-ARCHITECTURE.md](06-MULTI-SITE-ARCHITECTURE.md)  
6. [08-SECURITY-ARCHITECTURE.md](08-SECURITY-ARCHITECTURE.md)  
7. [26-NEXT-STEPS.md](26-NEXT-STEPS.md)  
8. ADRs in `DOCUMENTATION/decisions/`

---

## E. Reply template (copy/paste)

```text
STAGE 1 APPROVED: YES / NO

B1 Name: Pluto / other: ___
B2 Stack: PHP8.4+L13+F5 / other: ___
B3 Language: FA-RTL / EN / other: ___
B4 Permissions: Option A (teams) / Option B (global roles)
B5 Packages: Spatie trio YES / NO
B6 URLs: subdirectory + /admin confirmed YES / NO
B7 Central: single YES / NO
B8 Admin seed email: ___
B9 Storage: local MVP / S3 from start
B10 Backend-only scope confirmed: YES

Notes:
```
