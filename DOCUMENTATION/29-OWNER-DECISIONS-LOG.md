# 29 — Owner Decisions Log

Last updated: 2026-11-10

## Confirmed by owner

| ID | Decision | Owner answer | Effective choice |
|----|----------|--------------|------------------|
| B2 | Stack | تأیید | PHP 8.4 + Laravel 13 + Filament 5 |
| B3 | Admin language | پنل کاملاً فارسی | Filament FA + RTL |
| B8 | Super Admin | یک سوپر ادمین | Single seeded Super Admin |
| B9 | Storage | تصاویر و همه فایل‌ها روی همان سرور | **Local disks only for MVP** (no S3 required) |
| B4 | Permission model | ممکنه یه نفر تو یه سایت ادمین باشه و در جای دیگه خبرنگار | **Option A — Spatie teams / per-site roles** |
| AUTH | Login methods | هم OTP و هم یوزر و پسورد | **Dual auth:** mobile OTP **and** username/password |

## Auth details (approved)

1. Users may log in with **mobile + OTP**.
2. Users may log in with **username + password**.
   - Username identifier = **mobile** or **email** (whichever the account has).
3. Same session/security rules apply after either method (`is_active`, `is_blocked`, panel access).
4. SMS gateway is pluggable; default local/test driver = `log` until a production SMS provider is configured.

## Still optional / deferred (not blocking STAGE 2)

| Topic | Default used now | Later |
|-------|------------------|-------|
| SMS provider | `log` / mock driver | Set Kavenegar/Ghasedak/etc. via `.env` |
| Super Admin seed identity | `.env` / seeder defaults for local | Owner can change mobile/email/password in `.env` |
| S3 | Disabled | Can enable Flysystem disk later without schema rewrite |

## Storage explanation (resolved)

All media and attachments stay on the application server (`storage/` local disks). Public vs private disks remain separate locally. Remote object storage is out of MVP scope.

## Permission Option A (resolved)

A user can hold **different roles on different sites** (example: Regional Admin on Tehran, Journalist on Fars). Implemented via Spatie permission **teams** mapped to `site_id`, plus `site_user` membership.
