<?php

namespace App\Services\Auth;

use App\Models\LoginEvent;
use App\Models\User;
use App\Services\Notifications\SiteNotificationService;
use Illuminate\Http\Request;

class LoginAuditor
{
    public function record(
        string $event,
        ?User $user = null,
        ?string $method = null,
        ?string $mobile = null,
        ?string $email = null,
        ?array $meta = null,
        ?Request $request = null,
    ): void {
        $request ??= request();

        $row = LoginEvent::query()->create([
            'user_id' => $user?->id,
            'mobile' => $mobile ?? $user?->mobile,
            'email' => $email ?? $user?->email,
            'event' => $event,
            'method' => $method,
            'ip' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 512),
            'meta' => $meta,
            'created_at' => now(),
        ]);

        if ($event === 'login_failed') {
            app(SiteNotificationService::class)->maybeNotifyFailedLoginThreshold(
                $row->mobile,
                $row->ip,
            );
        }
    }
}
