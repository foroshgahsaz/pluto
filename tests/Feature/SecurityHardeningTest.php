<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Auth\OtpService;
use App\Services\Sms\LogSmsGateway;
use App\Support\Security\HtmlSanitizer;
use App\Support\Security\SafeZipExtractor;
use App\Support\Tenancy\AuthorizedSiteOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_authorized_site_options_exclude_foreign_sites(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran', 'name' => 'Tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars', 'name' => 'Fars']);
        $user = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $user,
            $tehran->id,
            'journalist'
        );

        $this->actingAs($user->fresh());
        $options = AuthorizedSiteOptions::pluck($user->fresh());

        $this->assertArrayHasKey($tehran->id, $options);
        $this->assertArrayNotHasKey($fars->id, $options);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        AuthorizedSiteOptions::assertWritable($user->fresh(), $fars->id, 'articles.create');
    }

    public function test_html_sanitizer_strips_script_tags(): void
    {
        $clean = app(HtmlSanitizer::class)->clean('<p>سلام</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>');
        $this->assertStringContainsString('<p>سلام</p>', $clean);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_safe_zip_extractor_rejects_zip_slip(): void
    {
        $dir = storage_path('app/backup-temp/zip-slip-test');
        File::ensureDirectoryExists($dir);
        $archive = $dir.'/evil.zip';

        $zip = new ZipArchive;
        $zip->open($archive, ZipArchive::CREATE);
        $zip->addFromString('../evil.txt', 'pwned');
        $zip->close();

        $extractTo = $dir.'/out';
        File::ensureDirectoryExists($extractTo);

        $this->expectException(\RuntimeException::class);
        app(SafeZipExtractor::class)->extract($archive, $extractTo);
    }

    public function test_safe_zip_extractor_extracts_normal_archive(): void
    {
        $dir = storage_path('app/backup-temp/zip-ok-test');
        File::ensureDirectoryExists($dir);
        $archive = $dir.'/ok.zip';
        $zip = new ZipArchive;
        $zip->open($archive, ZipArchive::CREATE);
        $zip->addFromString('db-dumps/sqlite.sql', 'SELECT 1;');
        $zip->close();

        $extractTo = $dir.'/out';
        $files = app(SafeZipExtractor::class)->extract($archive, $extractTo);
        $this->assertContains('db-dumps/sqlite.sql', $files);
        $this->assertFileExists($extractTo.'/db-dumps/sqlite.sql');
    }

    public function test_log_sms_gateway_redacts_otp_digits(): void
    {
        Log::spy();
        app(LogSmsGateway::class)->send('09121234567', 'کد ورود پلوتون: 123456');
        Log::shouldHaveReceived('info')->withArgs(function (string $message, array $context): bool {
            return $message === 'sms.send'
                && ! str_contains((string) $context['message'], '123456')
                && str_contains((string) $context['mobile'], '*');
        });
    }

    public function test_otp_request_blocked_when_production_uses_log_driver(): void
    {
        app()->detectEnvironment(fn () => 'production');
        config([
            'pluto.otp.driver' => 'log',
            'pluto.security.block_log_otp_in_production' => true,
        ]);

        $user = User::factory()->create(['mobile' => '09123334455']);

        try {
            app(OtpService::class)->request($user->mobile);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('mobile', $e->errors());
        } finally {
            app()->detectEnvironment(fn () => 'testing');
        }
    }

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $site = Site::factory()->create(['slug' => 'central', 'type' => \App\Enums\SiteType::Central, 'is_active' => true]);
        $response = $this->get('/');
        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
