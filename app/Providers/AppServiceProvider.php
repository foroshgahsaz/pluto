<?php

namespace App\Providers;

use App\Contracts\MalwareScanner;
use App\Contracts\SmsGateway;
use App\Listeners\RecordFailedBackup;
use App\Listeners\RecordSuccessfulBackup;
use App\Models\User;
use App\Services\Media\NullMalwareScanner;
use App\Services\Sms\LogSmsGateway;
use App\Support\Tenancy\SiteContext;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SiteContext::class);

        $this->app->bind(SmsGateway::class, function () {
            return match (config('pluto.otp.driver', 'log')) {
                default => new LogSmsGateway,
            };
        });

        $this->app->bind(MalwareScanner::class, NullMalwareScanner::class);
    }

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });

        Event::listen(BackupWasSuccessful::class, RecordSuccessfulBackup::class);
        Event::listen(BackupHasFailed::class, RecordFailedBackup::class);

        if (config('pluto.security.force_https')) {
            URL::forceScheme('https');
        }
    }
}
