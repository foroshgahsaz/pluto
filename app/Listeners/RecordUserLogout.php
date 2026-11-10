<?php

namespace App\Listeners;

use App\Services\Auth\LoginAuditor;
use Illuminate\Auth\Events\Logout;

class RecordUserLogout
{
    public function __construct(
        protected LoginAuditor $auditor,
    ) {}

    public function handle(Logout $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        $this->auditor->record(
            'logout',
            user: $user instanceof \App\Models\User ? $user : null,
            method: 'session',
            mobile: $user->mobile ?? null,
            email: $user->email ?? null,
        );
    }
}
