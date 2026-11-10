<?php

namespace Tests\Feature;

use App\Contracts\MalwareScanner;
use App\Contracts\SmsGateway;
use App\Models\Article;
use App\Models\LoginEvent;
use App\Models\Site;
use App\Models\Theme;
use App\Models\User;
use App\Services\Media\ClamAvMalwareScanner;
use App\Services\Media\NullMalwareScanner;
use App\Services\Sms\KavenegarSmsGateway;
use App\Services\Sms\LogSmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class MasterPromptGapsTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_is_audited(): void
    {
        $user = User::factory()->create(['mobile' => '09120001122']);
        Auth::login($user);
        Auth::logout();

        $this->assertDatabaseHas('login_events', [
            'event' => 'logout',
            'user_id' => $user->id,
            'mobile' => '09120001122',
        ]);
        $this->assertGreaterThanOrEqual(1, LoginEvent::query()->where('event', 'logout')->where('user_id', $user->id)->count());
    }

    public function test_seeder_creates_multiple_themes(): void
    {
        $this->seed();

        $this->assertTrue(Theme::query()->where('key', 'default')->exists());
        $this->assertTrue(Theme::query()->where('key', 'ink-daily')->exists());
        $this->assertTrue(Theme::query()->where('key', 'field-report')->exists());
        $this->assertGreaterThanOrEqual(3, Theme::query()->count());
    }

    public function test_sms_and_malware_drivers_resolve_from_config(): void
    {
        config(['pluto.otp.driver' => 'log']);
        $this->assertInstanceOf(LogSmsGateway::class, app(SmsGateway::class));

        config(['pluto.otp.driver' => 'kavenegar']);
        $this->app->forgetInstance(SmsGateway::class);
        $this->assertInstanceOf(KavenegarSmsGateway::class, $this->app->make(SmsGateway::class));

        config(['pluto.media.malware_scanner' => 'null']);
        $this->app->forgetInstance(MalwareScanner::class);
        $this->assertInstanceOf(NullMalwareScanner::class, $this->app->make(MalwareScanner::class));

        config(['pluto.media.malware_scanner' => 'clamav']);
        $this->app->forgetInstance(MalwareScanner::class);
        $this->assertInstanceOf(ClamAvMalwareScanner::class, $this->app->make(MalwareScanner::class));
    }

    public function test_restore_dry_run_validates_archive_with_sql_dump(): void
    {
        $dir = storage_path('app/backup-temp/test-restore');
        File::ensureDirectoryExists($dir);
        $archive = $dir.'/sample.zip';
        $zip = new ZipArchive;
        $zip->open($archive, ZipArchive::CREATE);
        $zip->addFromString('db-dumps/sqlite.sql', 'SELECT 1;');
        $zip->addFromString('storage/app/public/.gitkeep', '');
        $zip->close();

        $this->artisan('pluto:restore', [
            'archive' => $archive,
            '--dry-run' => true,
        ])->assertSuccessful();
    }

    public function test_public_article_listing_stays_within_query_budget(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        Article::factory()->published()->count(15)->create(['site_id' => $site->id]);

        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $this->get('/tehran/news')->assertOk();

        $this->assertLessThan(40, $queries, "Expected lean query count, got {$queries}");
    }

    public function test_password_reset_routes_are_registered(): void
    {
        $this->get('/admin/password-reset/request')->assertOk();
    }
}
