# ADR-005 — Mobile + OTP Admin Login

## Status
Accepted (owner confirmed dual login)

## Context
Owner requires every management user to access Filament with **mobile + OTP** and also with **username + password**. Roles may differ per site.

## Decision
- Dual admin authentication:
  1. Mobile + OTP
  2. Username/password (username = mobile or email)
- `users.mobile` unique; required for OTP-capable accounts
- `users.email` optional/unique nullable
- `users.password` required for password login path
- Pluggable SMS gateway (`SmsGateway`) with `log`/`array` drivers for local/tests
- Strict rate limits, OTP hashing at rest, short TTL, audit events
- Filament custom Persian login supporting both methods

## Alternatives considered
1. Password-only (rejected)
2. OTP-only (rejected — owner wants both)
3. Hard-coding one SMS vendor in domain logic (rejected — use adapter)

## Consequences
- STAGE 2 includes custom Filament auth pages
- Production SMS credentials configured later via `.env`
- Super Admin seed includes mobile + password
- Security tests cover both login paths + blocked users
