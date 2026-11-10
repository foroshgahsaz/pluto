# 02 — Requirements

## In scope (backend + Filament admin)

### Phase 1 — Core infrastructure
- Multi-site management (central + regional, subdirectory slugs)
- Site activate/deactivate, site-specific configuration
- Users, custom roles, granular permissions, site membership
- Tenant isolation (`site_id`) on all site-scoped data

### Phase 2 — Editorial + public data infrastructure
- Articles, categories/subcategories, tags, keywords, authors
- Workflow: draft, review/approve, publish now, schedule, revise
- Revisions, attachments, featured/selected flags
- Backend structures for homepage sections, archive, search, static pages
- **No public frontend templates**

### Phase 3 — Forms + appearance backend
- Dynamic form builder (fields, steps, conditions, submissions, export)
- Themes, logos, colors, menus, homepage section order (stored data only)

### Phase 4 — Admin features
- Dashboard statistics and filtered reports
- Internal notifications
- Audit logging, backups, restore procedures

### Phase 5 — Quality
- Automated tests (especially multi-site security)
- Full documentation under `DOCUMENTATION/`
- Installation and deployment guides

## Explicitly out of scope (until owner approval)

- Public homepage / article / category / archive UI
- Public theme rendering / visitor layouts
- Unapproved public APIs
- React / Vue introduction
- Separate Laravel app per region
- Features not listed in the official master scope

## Non-functional requirements

| Area | Requirement |
|------|-------------|
| AuthN | Laravel authentication, rate-limited login, secure sessions |
| AuthZ | Policies + permissions + site membership (server-side) |
| Isolation | Global scopes / equivalent; no IDOR across sites |
| Files | Type validation, safe names, private disk + authorized download |
| Audit | Sensitive actions logged without secrets |
| Integrity | Transactions for multi-step writes; FKs and unique constraints |
| Testability | Pest tests for isolation, permissions, publishing, forms, media |
| Docs | Always synchronized with implemented reality |

## Acceptance summary (final delivery)

System is production-ready only when multi-site isolation, Filament admin, editorial, media, forms, settings, dashboard, notifications, security tests, backup/restore, and documentation are **implemented, tested, and verified**—not merely sketched.
