# ADR-005 — Mobile + OTP Admin Login

## Status
Proposed (owner requested; implementation pending SMS/provider answers)

## Context
Owner requires every management user to access the Filament admin with mobile number and OTP, not only password forms.

## Decision (proposed)
- Primary admin authentication: **mobile + OTP**
- `users.mobile` unique and required for login-capable accounts
- `users.email` optional (useful for notifications later)
- Pluggable SMS gateway (`SmsGateway` interface) with `log`/`array` drivers for local/tests
- Strict rate limits, OTP hashing at rest, short TTL, audit events
- Filament custom Persian login flow

## Alternatives considered
1. Password-only (rejected by owner)
2. Password + optional OTP 2FA (does not match “login with mobile OTP” as primary)
3. Hard-coding one SMS vendor in domain logic (rejected — use adapter)

## Consequences
- STAGE 2 auth work expands beyond default Filament login
- Need SMS credentials in secrets for real environments
- Super Admin seed must include a mobile number
- Additional security tests for OTP abuse and blocked users
