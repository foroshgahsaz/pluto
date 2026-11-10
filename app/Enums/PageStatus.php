<?php

namespace App\Enums;

enum PageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::Published => 'منتشر شده',
        };
    }
}
