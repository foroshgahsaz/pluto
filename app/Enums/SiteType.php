<?php

namespace App\Enums;

enum SiteType: string
{
    case Central = 'central';
    case Regional = 'regional';

    public function label(): string
    {
        return match ($this) {
            self::Central => 'مرکزی',
            self::Regional => 'منطقه‌ای',
        };
    }
}
