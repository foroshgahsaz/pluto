<?php

return [
    'otp' => [
        'length' => (int) env('OTP_LENGTH', 6),
        'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 180),
        'resend_seconds' => (int) env('OTP_RESEND_SECONDS', 60),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'testing_code' => env('OTP_TESTING_CODE', '123456'),
        'driver' => env('OTP_SMS_DRIVER', 'log'),
    ],

    'seed' => [
        'super_admin_name' => env('SEED_SUPER_ADMIN_NAME', 'مدیر سیستم'),
        'super_admin_mobile' => env('SEED_SUPER_ADMIN_MOBILE', '09120000000'),
        'super_admin_email' => env('SEED_SUPER_ADMIN_EMAIL', 'admin@pluto.test'),
        'super_admin_password' => env('SEED_SUPER_ADMIN_PASSWORD', 'ChangeMe!23456'),
        'central_site_name' => env('SEED_CENTRAL_SITE_NAME', 'سایت مرکزی'),
        'central_site_slug' => env('SEED_CENTRAL_SITE_SLUG', 'central'),
    ],
];
