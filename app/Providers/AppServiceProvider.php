<?php

namespace App\Providers;

use App\Contracts\SmsGateway;
use App\Models\User;
use App\Services\Sms\LogSmsGateway;
use App\Support\Tenancy\SiteContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
    }

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });
    }
}
