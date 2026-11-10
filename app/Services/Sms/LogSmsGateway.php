<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): void
    {
        // Never log OTP plaintext — redact digit sequences of typical OTP length.
        $redacted = preg_replace('/\b\d{4,8}\b/', '******', $message) ?? '[redacted]';

        Log::info('sms.send', [
            'mobile' => $this->maskMobile($mobile),
            'message' => $redacted,
            'driver' => 'log',
        ]);
    }

    protected function maskMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?? '';
        if (strlen($digits) < 6) {
            return '***';
        }

        return substr($digits, 0, 4).str_repeat('*', max(0, strlen($digits) - 6)).substr($digits, -2);
    }
}
