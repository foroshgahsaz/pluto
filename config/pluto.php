<?php

return [
    'otp' => [
        'length' => (int) env('OTP_LENGTH', 6),
        'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 180),
        'resend_seconds' => (int) env('OTP_RESEND_SECONDS', 60),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'testing_code' => env('OTP_TESTING_CODE', '123456'),
        'driver' => env('OTP_SMS_DRIVER', 'log'),
        'kavenegar_api_key' => env('KAVENEGAR_API_KEY'),
        'kavenegar_sender' => env('KAVENEGAR_SENDER'),
        'ghasedak_api_key' => env('GHASEDAK_API_KEY'),
        'ghasedak_line' => env('GHASEDAK_LINE'),
    ],

    'seed' => [
        'super_admin_name' => env('SEED_SUPER_ADMIN_NAME', 'مدیر سیستم'),
        'super_admin_mobile' => env('SEED_SUPER_ADMIN_MOBILE', '09120000000'),
        'super_admin_email' => env('SEED_SUPER_ADMIN_EMAIL', 'admin@pluto.test'),
        'super_admin_password' => env('SEED_SUPER_ADMIN_PASSWORD', 'ChangeMe!23456'),
        'central_site_name' => env('SEED_CENTRAL_SITE_NAME', 'سایت مرکزی'),
        'central_site_slug' => env('SEED_CENTRAL_SITE_SLUG', 'central'),
    ],

    'security' => [
        'failed_login_threshold' => (int) env('FAILED_LOGIN_THRESHOLD', 5),
        'failed_login_window_minutes' => (int) env('FAILED_LOGIN_WINDOW_MINUTES', 15),
        'force_https' => (bool) env('FORCE_HTTPS', env('APP_ENV') === 'production'),
        'block_log_otp_in_production' => (bool) env('BLOCK_LOG_OTP_IN_PRODUCTION', true),
        'session_secure_cookie' => env('SESSION_SECURE_COOKIE'),
    ],

    'media' => [
        'max_image_kb' => (int) env('MEDIA_MAX_IMAGE_KB', 5120),
        'max_document_kb' => (int) env('MEDIA_MAX_DOCUMENT_KB', 20480),
        'allowed_image_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ],
        'allowed_document_mimes' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ],
        'blocked_extensions' => [
            'php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'com', 'js', 'html', 'htm', 'shtml', 'cgi', 'pl',
        ],
        'public_disk' => 'public',
        'private_disk' => 'local',
        'malware_scanner' => env('MALWARE_SCANNER', 'null'),
        'clamav_binary' => env('CLAMAV_BINARY', 'clamscan'),
    ],
];
