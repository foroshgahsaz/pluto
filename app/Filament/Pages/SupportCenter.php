<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SupportCenter extends Page
{
    protected string $view = 'filament.pages.support-center';

    protected static ?string $navigationLabel = 'پشتیبانی';

    protected static ?string $title = 'پشتیبانی و راهنما';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static string|UnitEnum|null $navigationGroup = 'امنیت و عملیات';

    protected static ?int $navigationSort = 100;

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
