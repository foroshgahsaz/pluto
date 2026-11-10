<?php

namespace App\Services\Auth;

use App\Contracts\SmsGateway;
use App\Models\OtpChallenge;
use App\Models\User;
use App\Support\MobileNormalizer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class OtpService
{
    public function __construct(
        protected SmsGateway $sms,
        protected LoginAuditor $auditor,
    ) {}

    public function request(string $mobile): void
    {
        $this->assertProductionSmsDriver();

        $normalized = MobileNormalizer::normalize($mobile);

        if (! MobileNormalizer::isValidIranianMobile($normalized)) {
            throw ValidationException::withMessages([
                'mobile' => 'شماره موبایل معتبر نیست.',
            ]);
        }

        $user = User::query()->where('mobile', $normalized)->first();

        if (! $user || ! $user->is_active || $user->is_blocked) {
            $this->auditor->record('otp_request_denied', user: $user, method: 'otp', mobile: $normalized);
            throw ValidationException::withMessages([
                'mobile' => 'امکان ارسال کد برای این شماره وجود ندارد.',
            ]);
        }

        $recent = OtpChallenge::query()
            ->where('mobile', $normalized)
            ->where('created_at', '>=', now()->subSeconds((int) config('pluto.otp.resend_seconds', 60)))
            ->whereNull('consumed_at')
            ->exists();

        if ($recent) {
            throw ValidationException::withMessages([
                'mobile' => 'لطفاً کمی بعد دوباره تلاش کنید.',
            ]);
        }

        $code = $this->generateCode();

        OtpChallenge::query()->create([
            'mobile' => $normalized,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addSeconds((int) config('pluto.otp.ttl_seconds', 180)),
            'attempts' => 0,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 512),
            'created_at' => now(),
        ]);

        $this->sms->send($normalized, "کد ورود پلوتون: {$code}");
        $this->auditor->record('otp_requested', user: $user, method: 'otp', mobile: $normalized);
    }

    public function verify(string $mobile, string $code): User
    {
        $normalized = MobileNormalizer::normalize($mobile);

        if (! MobileNormalizer::isValidIranianMobile($normalized)) {
            throw ValidationException::withMessages([
                'mobile' => 'شماره موبایل معتبر نیست.',
            ]);
        }

        $challenge = OtpChallenge::query()
            ->where('mobile', $normalized)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $challenge || $challenge->isExpired()) {
            $this->auditor->record('otp_verify_failed', method: 'otp', mobile: $normalized, meta: ['reason' => 'expired_or_missing']);
            throw ValidationException::withMessages([
                'code' => 'کد واردشده نامعتبر یا منقضی است.',
            ]);
        }

        $maxAttempts = (int) config('pluto.otp.max_attempts', 5);

        if ($challenge->attempts >= $maxAttempts) {
            $this->auditor->record('otp_verify_failed', method: 'otp', mobile: $normalized, meta: ['reason' => 'max_attempts']);
            throw ValidationException::withMessages([
                'code' => 'تعداد تلاش‌ها بیش از حد مجاز است.',
            ]);
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');
            $this->auditor->record('otp_verify_failed', method: 'otp', mobile: $normalized, meta: ['reason' => 'bad_code']);
            throw ValidationException::withMessages([
                'code' => 'کد واردشده نامعتبر است.',
            ]);
        }

        $user = User::query()->where('mobile', $normalized)->first();

        if (! $user || ! $user->is_active || $user->is_blocked) {
            $this->auditor->record('otp_verify_failed', user: $user, method: 'otp', mobile: $normalized, meta: ['reason' => 'inactive']);
            throw ValidationException::withMessages([
                'code' => 'دسترسی این حساب غیرفعال است.',
            ]);
        }

        $challenge->forceFill([
            'consumed_at' => now(),
        ])->save();

        if ($user->mobile_verified_at === null) {
            $user->forceFill(['mobile_verified_at' => now()])->save();
        }

        $this->auditor->record('otp_verify_success', user: $user, method: 'otp', mobile: $normalized);

        return $user;
    }

    protected function assertProductionSmsDriver(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        if (! config('pluto.security.block_log_otp_in_production', true)) {
            return;
        }

        if (config('pluto.otp.driver', 'log') === 'log') {
            throw ValidationException::withMessages([
                'mobile' => 'ارسال OTP در محیط production با درایور log مجاز نیست.',
            ]);
        }
    }

    protected function generateCode(): string
    {
        $length = (int) config('pluto.otp.length', 6);

        if (app()->environment('testing') && config('pluto.otp.testing_code')) {
            return (string) config('pluto.otp.testing_code');
        }

        $max = (10 ** $length) - 1;
        $code = (string) random_int(0, $max);

        return str_pad($code, $length, '0', STR_PAD_LEFT);
    }

    public function peekTestingCode(): string
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException('Testing OTP peek is only available in testing.');
        }

        return (string) config('pluto.otp.testing_code', '123456');
    }
}
