<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class KavenegarSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): void
    {
        $apiKey = (string) config('pluto.otp.kavenegar_api_key');
        if ($apiKey === '') {
            throw new RuntimeException('KAVENEGAR_API_KEY is not configured.');
        }

        $sender = config('pluto.otp.kavenegar_sender');
        $url = "https://api.kavenegar.com/v1/{$apiKey}/sms/send.json";

        $response = Http::asForm()->post($url, array_filter([
            'receptor' => $mobile,
            'message' => $message,
            'sender' => $sender,
        ]));

        if (! $response->successful()) {
            Log::error('sms.kavenegar_failed', [
                'mobile' => $mobile,
                'status' => $response->status(),
            ]);
            throw new RuntimeException('ارسال پیامک کاوه‌نگار ناموفق بود.');
        }
    }
}
