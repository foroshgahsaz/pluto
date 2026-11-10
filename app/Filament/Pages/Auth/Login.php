<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\Auth\LoginAuditor;
use App\Services\Auth\OtpService;
use App\Support\MobileNormalizer;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;

class Login extends BaseLogin
{
    #[Url]
    public string $mode = 'password';

    public function getTitle(): string|Htmlable
    {
        return 'ورود به پنل پلوتون';
    }

    public function getHeading(): string|Htmlable|null
    {
        return $this->mode === 'otp'
            ? 'ورود با کد یکبارمصرف'
            : 'ورود با نام کاربری و رمز عبور';
    }

    public function switchToOtp(): void
    {
        $this->mode = 'otp';
        $this->cacheSchema('form', null);
        $this->form->fill();
    }

    public function switchToPassword(): void
    {
        $this->mode = 'password';
        $this->cacheSchema('form', null);
        $this->form->fill();
    }

    public function getFormActions(): array
    {
        if ($this->mode === 'otp') {
            return [
                $this->getRequestOtpFormAction(),
                $this->getVerifyOtpFormAction(),
                $this->getSwitchToPasswordAction(),
            ];
        }

        return [
            $this->getAuthenticateFormAction(),
            $this->getSwitchToOtpAction(),
        ];
    }

    protected function getSwitchToOtpAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('switchToOtp')
            ->label('ورود با OTP')
            ->link()
            ->action('switchToOtp');
    }

    protected function getSwitchToPasswordAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('switchToPassword')
            ->label('ورود با رمز عبور')
            ->link()
            ->action('switchToPassword');
    }

    protected function getRequestOtpFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('requestOtp')
            ->label('ارسال کد')
            ->color('gray')
            ->action('requestOtp');
    }

    protected function getVerifyOtpFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('verifyOtp')
            ->label('تأیید و ورود')
            ->submit('verifyOtp');
    }

    public function requestOtp(OtpService $otpService): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $data = $this->form->getState();
        $otpService->request((string) ($data['mobile'] ?? ''));

        Notification::make()
            ->title('کد ارسال شد')
            ->body('اگر شماره معتبر باشد، کد یکبارمصرف ارسال می‌شود.')
            ->success()
            ->send();
    }

    public function verifyOtp(OtpService $otpService): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $user = $otpService->verify((string) ($data['mobile'] ?? ''), (string) ($data['code'] ?? ''));

        if (! $this->isUserAllowedToAccessPanel($user)) {
            app(LoginAuditor::class)->record('login_failed', user: $user, method: 'otp', mobile: $user->mobile, meta: ['reason' => 'panel_denied']);
            throw ValidationException::withMessages([
                'data.code' => 'دسترسی به پنل برای این حساب مجاز نیست.',
            ]);
        }

        Auth::guard(Filament::getAuthGuard())->login($user, false);
        session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ])->save();

        app(LoginAuditor::class)->record('login_success', user: $user, method: 'otp');

        return app(LoginResponse::class);
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        if ($this->mode === 'otp') {
            return $schema
                ->components([
                    TextInput::make('mobile')
                        ->label('شماره موبایل')
                        ->tel()
                        ->required()
                        ->autofocus(),
                    TextInput::make('code')
                        ->label('کد یکبارمصرف')
                        ->numeric()
                        ->length(6)
                        ->required()
                        ->autocomplete('one-time-code'),
                ]);
        }

        return parent::form($schema);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('موبایل یا ایمیل')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $login = trim((string) ($data['login'] ?? ''));

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => $login,
                'password' => $data['password'],
            ];
        }

        return [
            'mobile' => MobileNormalizer::normalize($login),
            'password' => $data['password'],
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response) {
            /** @var User|null $user */
            $user = Filament::auth()->user();

            if ($user) {
                $user->forceFill([
                    'last_login_at' => now(),
                    'last_login_ip' => request()->ip(),
                ])->save();

                app(LoginAuditor::class)->record('login_success', user: $user, method: 'password');
            }
        }

        return $response;
    }

    protected function throwFailureValidationException(): never
    {
        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);

        app(LoginAuditor::class)->record(
            'login_failed',
            method: 'password',
            mobile: $credentials['mobile'] ?? null,
            email: $credentials['email'] ?? null,
        );

        event(new Failed(Filament::getAuthGuard(), null, $credentials));

        throw ValidationException::withMessages([
            'data.login' => 'اطلاعات ورود نادرست است.',
        ]);
    }
}
