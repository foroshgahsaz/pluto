<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Auth\OtpService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_inactive_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->inactive()->create();

        $this->assertFalse($user->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_blocked_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->blocked()->create();

        $this->assertFalse($user->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_active_user_can_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_password_login_works_with_mobile_identifier(): void
    {
        $user = User::factory()->create([
            'mobile' => '09121112233',
            'password' => Hash::make('SecretPass!123'),
        ]);

        $this->assertTrue(auth()->attempt([
            'mobile' => '09121112233',
            'password' => 'SecretPass!123',
        ]));

        $this->assertTrue(auth()->user()->is($user));
    }

    public function test_otp_login_succeeds_with_valid_code(): void
    {
        $user = User::factory()->create(['mobile' => '09123334455']);
        $otp = app(OtpService::class);

        $otp->request('09123334455');
        $verified = $otp->verify('09123334455', config('pluto.otp.testing_code'));

        $this->assertTrue($verified->is($user));
    }

    public function test_otp_login_rejects_invalid_code(): void
    {
        User::factory()->create(['mobile' => '09123334455']);
        $otp = app(OtpService::class);
        $otp->request('09123334455');

        $this->expectException(ValidationException::class);
        $otp->verify('09123334455', '000000');
    }

    public function test_inactive_user_cannot_request_otp(): void
    {
        User::factory()->inactive()->create(['mobile' => '09125556677']);
        $otp = app(OtpService::class);

        $this->expectException(ValidationException::class);
        $otp->request('09125556677');
    }

    public function test_regional_admin_cannot_promote_self_to_super_admin(): void
    {
        $regional = User::factory()->create(['is_super_admin' => false]);
        $service = app(AssignSiteRole::class);

        $this->expectException(AuthorizationException::class);
        $service->promoteSuperAdmin($regional, $regional);
    }

    public function test_regional_admin_cannot_promote_other_user_to_super_admin(): void
    {
        $regional = User::factory()->create(['is_super_admin' => false]);
        $target = User::factory()->create(['is_super_admin' => false]);
        $service = app(AssignSiteRole::class);

        $this->expectException(AuthorizationException::class);
        $service->promoteSuperAdmin($regional, $target);
    }

    public function test_super_admin_can_promote_other_user(): void
    {
        $super = User::factory()->create(['is_super_admin' => true]);
        $target = User::factory()->create(['is_super_admin' => false]);

        app(AssignSiteRole::class)->promoteSuperAdmin($super, $target);

        $this->assertTrue($target->fresh()->isSuperAdmin());
    }
}
