<?php

namespace App\Enums;

enum HomepageSectionType: string
{
    case Latest = 'latest';
    case Featured = 'featured';
    case Selected = 'selected';
    case Category = 'category';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Latest => 'آخرین اخبار',
            self::Featured => 'اخبار ویژه',
            self::Selected => 'اخبار منتخب',
            self::Category => 'اخبار یک دسته',
            self::Custom => 'سفارشی',
        };
    }
}
