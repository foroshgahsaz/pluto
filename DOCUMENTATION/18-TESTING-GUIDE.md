# 18 — Testing Guide

## Framework
Pest (preferred) with Laravel testing helpers. Feature tests for HTTP/Filament; unit tests for Actions/Services.

## Priority order
1. Multi-site isolation & authorization
2. Editorial workflow transitions
3. Media private access
4. Form validation & isolation
5. Scheduling/publish jobs
6. Backup metadata/permissions
7. Dashboard scope filtering

## Mandatory security scenarios
See checklist items 1–10 in [09-SECURITY-CHECKLIST.md](09-SECURITY-CHECKLIST.md).

Each must include:
- Positive test (authorized user succeeds)
- Negative test (unauthorized fails with 403/404)

## OTP auth scenarios (added by owner requirement)
1. Valid mobile + valid OTP logs the user into Filament.
2. Expired/invalid OTP is rejected.
3. Rate limit blocks OTP spam for same mobile/IP.
4. Inactive or blocked user cannot obtain a session even with correct OTP.
5. OTP codes never appear in application logs in production driver paths.
6. Users without membership/permission still cannot cross sites after login.

## Filament testing
Use Livewire test helpers against resource pages/actions. Do not treat “button hidden” as proof of security; also call actions/endpoints directly.

## How to run (after scaffold)
```bash
php artisan test
# or
./vendor/bin/pest
```

## Evidence rule
`25-PROJECT-STATUS.md` may mark a feature **Tested** only when command output was actually executed and summarized in the stage report.
