<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GhasedakSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): void
    {
        $apiKey = (string) config('pluto.otp.ghasedak_api_key');
        if ($apiKey === '') {
            throw new RuntimeException('GHASEDAK_API_KEY is not configured.');
        }

        $line = config('pluto.otp.ghasedak_line');
        $response = Http::withHeaders([
            'apikey' => $apiKey,
        ])->asForm()->post('https://gateway.ghasedak.me/rest/api/v1/WebService/SendSingleSMS', array_filter([
            'receptor' => $mobile,
            'message' => $message,
            'linenumber' => $line,
        ]));

        if (! $response->successful()) {
            Log::error('sms.ghasedak_failed', [
                'mobile' => $mobile,
                'status' => $response->status(),
            ]);
            throw new RuntimeException('ارسال پیامک قاصدک ناموفق بود.');
        }
    }
}
