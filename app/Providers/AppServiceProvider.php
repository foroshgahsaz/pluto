<?php

namespace App\Providers;

use App\Contracts\MalwareScanner;
use App\Contracts\SmsGateway;
use App\Listeners\RecordFailedBackup;
use App\Listeners\RecordSuccessfulBackup;
use App\Listeners\RecordUserLogout;
use App\Models\User;
use App\Services\Media\ClamAvMalwareScanner;
use App\Services\Media\NullMalwareScanner;
use App\Services\Sms\GhasedakSmsGateway;
use App\Services\Sms\KavenegarSmsGateway;
use App\Services\Sms\LogSmsGateway;
use App\Support\Tenancy\SiteContext;
use Illuminate\Auth\Events\Logout;
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
                'kavenegar' => new KavenegarSmsGateway,
                'ghasedak' => new GhasedakSmsGateway,
                default => new LogSmsGateway,
            };
        });

        $this->app->bind(MalwareScanner::class, function () {
            return match (config('pluto.media.malware_scanner', 'null')) {
                'clamav' => new ClamAvMalwareScanner,
                default => new NullMalwareScanner,
            };
        });
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
        Event::listen(Logout::class, RecordUserLogout::class);

        if (config('pluto.security.force_https')) {
            URL::forceScheme('https');
        }
    }
}
