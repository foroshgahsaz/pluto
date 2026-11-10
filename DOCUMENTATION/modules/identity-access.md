# Module: Identity & Access

**Status:** Implemented (STAGE 2 core) · Tested

## Module purpose
Authenticate admin users and authorize actions with per-site roles.

## Module responsibilities
- Dual login: password (mobile/email) and mobile OTP
- Super admin flag (`is_super_admin`)
- Spatie permission teams mapped to `site_id`
- Login auditing
- User/site assignment

## Related source files
- `app/Models/User.php`
- `app/Services/Auth/OtpService.php`
- `app/Services/Auth/LoginAuditor.php`
- `app/Services/Access/AssignSiteRole.php`
- `app/Filament/Pages/Auth/Login.php`
- `app/Filament/Resources/Users/*`
- `app/Policies/UserPolicy.php`
- `database/seeders/RolesAndPermissionsSeeder.php`

## Login methods
1. Password: identifier = mobile or email
2. OTP: request code via SMS gateway (`log` driver locally), verify hashed challenge

## Testing procedures
`tests/Feature/AuthSecurityTest.php`, `tests/Feature/SeederBootstrapTest.php`

## Security considerations
OTP hashed at rest; rate limits on login actions; inactive/blocked users denied panel access; only Super Admin can promote Super Admin.
