# 29 — Owner Decisions Log

Last updated: 2026-11-10

## Confirmed by owner

| ID | Decision | Owner answer | Effective choice |
|----|----------|--------------|------------------|
| B2 | Stack | تأیید | PHP 8.4 + Laravel 13 + Filament 5 |
| B3 | Admin language | پنل کاملاً فارسی | Filament FA + RTL |
| B8 | Super Admin | یک سوپر ادمین می‌خواهم | Single seeded Super Admin |
| AUTH-OTP | Login method | ورود با موبایل + OTP برای همه سطوح مدیریتی | Phone + OTP login is **in scope** for admin auth |

## Explained to owner / awaiting final pick

| ID | Topic | Recommendation | Status |
|----|-------|----------------|--------|
| B9 | Storage local vs S3 | **Local for MVP** | Awaiting “باشه لوکال” |
| B4 | Permission Option A vs B | **Option A (نقش جدا per سایت)** | Awaiting pick (explained below) |
| B1 | Product name Pluto | Keep Pluto / پلوتون | Soft-assumed unless changed |
| B5 | Spatie packages | Approve trio | Soft-assumed with stack |
| B7 | Single central site | Yes | Soft-assumed |
| OTP-SMS | SMS provider | Need owner choice (Kavenegar / Ghasedak / other / mock) | **Required before coding OTP** |
| OTP-MODE | OTP-only vs OTP+password backup | OTP primary + optional password backup for Super Admin recovery | Awaiting confirmation |
| B8-ID | Super Admin identity | Need **mobile number** (and optional email) for seed — not password in chat | Required |

## Storage explanation (plain language)

فایل‌های سیستم (عکس خبر، فایل پیوست، بکاپ) باید جایی ذخیره شوند:

1. **لوکال (Local):** روی همان سروری که سایت نصب است، داخل پوشه `storage`.  
   - ساده برای شروع  
   - ارزان  
   - برای MVP کافی است  
   - اگر سرور خراب شود یا عوض شود، باید فایل‌ها را جداگانه بکاپ بگیرید

2. **S3 (یا سرویس شبیه S3 مثل MinIO / Arvan Object / Liara / Amazon S3):** فایل‌ها روی فضای ابری جداگانه  
   - امن‌تر برای بکاپ و مقیاس  
   - نیاز به حساب، کلید دسترسی، و هزینه دارد  
   - برای اول کار لازم نیست؛ بعداً بدون عوض کردن کل معماری قابل اضافه‌شدن است

**پیشنهاد من برای MVP:** لوکال. معماری طوری طراحی می‌شود که بعداً S3 را فقط با تنظیم `.env` فعال کنیم.

## Permission Option A vs B (plain language)

فرض: یک نفر هم روی سایت تهران کار می‌کند هم فارس.

- **Option A:** همان نفر می‌تواند در تهران «خبرنگار» باشد و در فارس «ویراستار». نقش‌ها per سایت جدا هستند.  
- **Option B:** نقش او سراسری است؛ اگر خبرنگار باشد، روی همه سایت‌های مجازش همان خبرنگار است.

**پیشنهاد من:** Option A (منعطف‌تر برای سازمان چندمنطقه‌ای).

## OTP / mobile login (new requirement)

Owner requirement: users at **every management level** must sign in with **mobile + OTP**.

### Proposed auth flow (pending approval)

1. User enters Iranian mobile (`09xxxxxxxxx`).
2. System rate-limits and sends OTP via SMS provider.
3. User enters OTP; on success, Laravel session starts for Filament.
4. Inactive/blocked users cannot complete login.
5. All OTP send/verify/fail events go to `login_events` audit table.
6. Super Admin is also OTP-based (same path). Keep an optional emergency password **only if owner wants**, otherwise OTP-only.

### Architecture impact

| Area | Change |
|------|--------|
| `users` table | Require unique `mobile`; email optional/nullable |
| Filament login | Custom FA login pages (request OTP → verify OTP) |
| Packages | Prefer Laravel notifications + SMS driver/gateway adapter; avoid abandoned OTP packages |
| Security | OTP expiry (e.g. 2–5 min), attempt limits, resend cooldown, no OTP in logs |
| Dev/test | `OTP_DRIVER=log` or `array` mock so tests work without real SMS |
| Docs/tests | Auth security tests must cover OTP paths |

### Blockers before implementing OTP

1. Which SMS provider for production?  
2. OTP-only, or OTP + backup password for Super Admin?  
3. Super Admin mobile number for local seeder (dev only).
