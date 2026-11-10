<?php

namespace App\Support;

class MobileNormalizer
{
    public static function normalize(?string $mobile): ?string
    {
        if ($mobile === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $mobile) ?? '';

        if (str_starts_with($digits, '98') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0'.$digits;
        }

        return $digits !== '' ? $digits : null;
    }

    public static function isValidIranianMobile(?string $mobile): bool
    {
        $normalized = self::normalize($mobile);

        return $normalized !== null && (bool) preg_match('/^09\d{9}$/', $normalized);
    }
}
