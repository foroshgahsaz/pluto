<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Lightweight Jalali formatter (no external package).
 */
class PersianDate
{
    private const MONTHS = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
        5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
        9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];

    private const WEEKDAYS = [
        'Saturday' => 'شنبه',
        'Sunday' => 'یکشنبه',
        'Monday' => 'دوشنبه',
        'Tuesday' => 'سه‌شنبه',
        'Wednesday' => 'چهارشنبه',
        'Thursday' => 'پنجشنبه',
        'Friday' => 'جمعه',
    ];

    public static function format(CarbonInterface $date, string $pattern = 'j F Y'): string
    {
        [$jy, $jm, $jd] = self::gregorianToJalali((int) $date->year, (int) $date->month, (int) $date->day);

        $replacements = [
            'l' => self::WEEKDAYS[$date->format('l')] ?? $date->format('l'),
            'j' => self::persianDigits((string) $jd),
            'd' => self::persianDigits(str_pad((string) $jd, 2, '0', STR_PAD_LEFT)),
            'F' => self::MONTHS[$jm] ?? (string) $jm,
            'm' => self::persianDigits(str_pad((string) $jm, 2, '0', STR_PAD_LEFT)),
            'n' => self::persianDigits((string) $jm),
            'Y' => self::persianDigits((string) $jy),
            'y' => self::persianDigits(substr((string) $jy, -2)),
            'H' => self::persianDigits($date->format('H')),
            'i' => self::persianDigits($date->format('i')),
        ];

        return strtr($pattern, $replacements);
    }

    public static function relative(?CarbonInterface $date): string
    {
        if (! $date) {
            return '—';
        }

        $diff = $date->diffInMinutes(now(), false);
        if ($diff < 1) {
            return 'همین الان';
        }
        if ($diff < 60) {
            return self::persianDigits((string) (int) $diff).' دقیقه پیش';
        }
        $hours = (int) floor($diff / 60);
        if ($hours < 24) {
            return self::persianDigits((string) $hours).' ساعت پیش';
        }

        return self::format($date, 'j F Y');
    }

    public static function persianDigits(string $value): string
    {
        return strtr($value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    public static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }
}
