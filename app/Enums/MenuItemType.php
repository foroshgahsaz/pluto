<?php

namespace App\Enums;

enum MenuItemType: string
{
    case Url = 'url';
    case Page = 'page';
    case Category = 'category';
    case Article = 'article';

    public function label(): string
    {
        return match ($this) {
            self::Url => 'لینک سفارشی',
            self::Page => 'صفحه',
            self::Category => 'دسته‌بندی',
            self::Article => 'خبر',
        };
    }
}
