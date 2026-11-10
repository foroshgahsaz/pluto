# Pluto — Technical Documentation Index

**Project:** Pluto (سامانه خبری پلوتون)  
**Type:** Enterprise Multi-Site News Management System  
**Stack (proposed):** Laravel 13 · Filament 5 · Livewire 4 · MySQL · Pest  
**Current stage:** STAGE 7 — Dashboard + Notifications  
**Status:** Implemented and tested on this branch; merge after Stage 6

---

## How to use this documentation

This folder is the **source of truth** for architecture, security, database design, modules, and delivery status.

A qualified Laravel developer must be able to install, operate, secure, and extend Pluto using only these documents and the repository source code.

---

## Document map

| # | File | Purpose |
|---|------|---------|
| 00 | [00-README.md](00-README.md) | Documentation index |
| 01 | [01-PROJECT-OVERVIEW.md](01-PROJECT-OVERVIEW.md) | Product and technical overview |
| 02 | [02-REQUIREMENTS.md](02-REQUIREMENTS.md) | Approved scope and explicit exclusions |
| 03 | [03-SYSTEM-ARCHITECTURE.md](03-SYSTEM-ARCHITECTURE.md) | Application architecture |
| 04 | [04-DATABASE-DESIGN.md](04-DATABASE-DESIGN.md) | Schema design and constraints |
| 05 | [05-DATABASE-ERD.md](05-DATABASE-ERD.md) | Entity-relationship diagrams |
| 06 | [06-MULTI-SITE-ARCHITECTURE.md](06-MULTI-SITE-ARCHITECTURE.md) | Tenant/site isolation model |
| 07 | [07-USER-ROLES-AND-PERMISSIONS.md](07-USER-ROLES-AND-PERMISSIONS.md) | Roles, permissions, site access |
| 08 | [08-SECURITY-ARCHITECTURE.md](08-SECURITY-ARCHITECTURE.md) | Security design |
| 09 | [09-SECURITY-CHECKLIST.md](09-SECURITY-CHECKLIST.md) | Verifiable security checklist |
| 10 | [10-NEWS-EDITORIAL-SYSTEM.md](10-NEWS-EDITORIAL-SYSTEM.md) | Editorial workflow (design) |
| 11 | [11-MEDIA-MANAGEMENT.md](11-MEDIA-MANAGEMENT.md) | Media/file design |
| 12 | [12-FORM-BUILDER.md](12-FORM-BUILDER.md) | Form builder design |
| 13 | [13-SITE-SETTINGS-AND-THEMES.md](13-SITE-SETTINGS-AND-THEMES.md) | Themes and settings design |
| 14 | [14-DASHBOARD-AND-REPORTS.md](14-DASHBOARD-AND-REPORTS.md) | Admin analytics design |
| 15 | [15-NOTIFICATIONS.md](15-NOTIFICATIONS.md) | Internal notifications design |
| 16 | [16-BACKUP-AND-RESTORE.md](16-BACKUP-AND-RESTORE.md) | Backup/restore design |
| 17 | [17-API-DOCUMENTATION.md](17-API-DOCUMENTATION.md) | API policy (none approved yet) |
| 18 | [18-TESTING-GUIDE.md](18-TESTING-GUIDE.md) | Testing strategy |
| 19 | [19-INSTALLATION-GUIDE.md](19-INSTALLATION-GUIDE.md) | Installation (pending scaffold) |
| 20 | [20-DEPLOYMENT-GUIDE.md](20-DEPLOYMENT-GUIDE.md) | Deployment (design) |
| 21 | [21-DEVELOPER-GUIDE.md](21-DEVELOPER-GUIDE.md) | Developer conventions |
| 22 | [22-CODING-STANDARDS.md](22-CODING-STANDARDS.md) | Coding standards |
| 23 | [23-TROUBLESHOOTING.md](23-TROUBLESHOOTING.md) | Troubleshooting |
| 24 | [24-CHANGELOG.md](24-CHANGELOG.md) | Changelog |
| 25 | [25-PROJECT-STATUS.md](25-PROJECT-STATUS.md) | Feature status matrix |
| 26 | [26-NEXT-STEPS.md](26-NEXT-STEPS.md) | Roadmap and approval gates |
| — | [27-STAGE1-INSPECTION-REPORT.md](27-STAGE1-INSPECTION-REPORT.md) | Stage 1 inspection findings |
| — | [28-APPROVAL-REQUEST.md](28-APPROVAL-REQUEST.md) | Decisions requiring owner approval |
| — | [29-OWNER-DECISIONS-LOG.md](29-OWNER-DECISIONS-LOG.md) | Confirmed owner decisions + plain explanations |

### Supporting folders

- `DOCUMENTATION/modules/` — per-module deep documentation (populated as modules are implemented)
- `DOCUMENTATION/diagrams/` — Mermaid/source diagrams
- `DOCUMENTATION/decisions/` — Architecture Decision Records (ADRs)

---

## Status legend

Used in `25-PROJECT-STATUS.md`:

| Status | Meaning |
|--------|---------|
| Not Started | Not designed or built |
| In Progress | Actively being designed or implemented |
| Implemented | Code exists |
| Tested | Automated/manual tests executed with recorded results |
| Verified | Reviewed against requirements and security rules |

---

## Current gate

**STAGE 7 dashboard + notifications implemented and tested on this branch.**  
Next: STAGE 8 backups + hardening after owner merge of the PR stack.  
See [25-PROJECT-STATUS.md](25-PROJECT-STATUS.md) and [26-NEXT-STEPS.md](26-NEXT-STEPS.md).
