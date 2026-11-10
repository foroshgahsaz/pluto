<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): void
    {
        Log::info('sms.send', [
            'mobile' => $mobile,
            'message' => $message,
        ]);
    }
}
