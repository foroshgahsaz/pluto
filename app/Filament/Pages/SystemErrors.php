<?php

namespace App\Filament\Pages;

use App\Services\Ops\SystemErrorReader;
use App\Support\Backup\BackupRunContext;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SystemErrors extends Page
{
    protected string $view = 'filament.pages.system-errors';

    protected static ?string $navigationLabel = 'نمایش خطاها';

    protected static ?string $title = 'نمایش خطاهای سامانه';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'امنیت و عملیات';

    protected static ?int $navigationSort = 91;

    /** @var list<\App\Models\BackupRecord> */
    public array $failedBackups = [];

    /** @var list<object> */
    public array $failedJobs = [];

    /** @var list<array{time:?string, level:string, message:string}> */
    public array $logErrors = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(SystemErrorReader $reader): void
    {
        $this->failedBackups = $reader->recentFailedBackups()->all();
        $this->failedJobs = $reader->recentFailedJobs()->all();
        $this->logErrors = $reader->recentLogErrors();
    }

    public function typeLabel(string $type): string
    {
        return BackupRunContext::label($type);
    }
}
