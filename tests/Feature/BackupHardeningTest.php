<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageBackups;
use App\Filament\Pages\SystemErrors;
use App\Models\BackupRecord;
use App\Models\User;
use App\Services\Backup\BackupRecorder;
use App\Services\Ops\SystemErrorReader;
use App\Support\Backup\BackupRunContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Tests\TestCase;

class BackupHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_successful_backup_event_creates_record_and_notifies_super_admin(): void
    {
        NotificationFacade::fake();
        $admin = User::factory()->create(['is_super_admin' => true, 'is_active' => true]);

        Storage::disk('local')->put('Pluto/2026-11-10-01-00-00.zip', 'zip-bytes');
        BackupRunContext::set(BackupRunContext::TYPE_DATABASE);

        try {
            $record = app(BackupRecorder::class)->recordSuccess(
                new BackupWasSuccessful('local', 'Pluto'),
                $admin,
            );
        } finally {
            BackupRunContext::clear();
        }

        $this->assertSame('success', $record->status);
        $this->assertSame('database', $record->type);
        $this->assertSame('local', $record->disk);
        $this->assertNotNull($record->path);
        $this->assertDatabaseHas('backup_records', ['id' => $record->id, 'status' => 'success', 'type' => 'database']);
        NotificationFacade::assertSentTo($admin, \Filament\Notifications\DatabaseNotification::class);
    }

    public function test_failed_backup_event_creates_failed_record(): void
    {
        NotificationFacade::fake();
        User::factory()->create(['is_super_admin' => true, 'is_active' => true]);
        BackupRunContext::set(BackupRunContext::TYPE_FILES);

        try {
            $record = app(BackupRecorder::class)->recordFailure(
                new BackupHasFailed(new \RuntimeException('disk full'), 'local', 'Pluto'),
            );
        } finally {
            BackupRunContext::clear();
        }

        $this->assertSame('failed', $record->status);
        $this->assertSame('files', $record->type);
        $this->assertSame('disk full', $record->message);
    }

    public function test_only_super_admin_can_access_manage_backups_page(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);
        $user = User::factory()->create(['is_super_admin' => false]);

        $this->actingAs($admin);
        $this->assertTrue(ManageBackups::canAccess());

        $this->actingAs($user);
        $this->assertFalse(ManageBackups::canAccess());
    }

    public function test_non_super_admin_cannot_download_backup(): void
    {
        Storage::disk('local')->put('Pluto/demo.zip', 'content');
        $backup = BackupRecord::factory()->create([
            'status' => 'success',
            'disk' => 'local',
            'path' => 'Pluto/demo.zip',
        ]);

        $user = User::factory()->create(['is_super_admin' => false]);

        $this->actingAs($user)
            ->get(route('backups.download', $backup))
            ->assertForbidden();
    }

    public function test_super_admin_can_download_existing_backup(): void
    {
        Storage::disk('local')->put('Pluto/demo.zip', 'content');
        $backup = BackupRecord::factory()->create([
            'status' => 'success',
            'disk' => 'local',
            'path' => 'Pluto/demo.zip',
        ]);
        $admin = User::factory()->create(['is_super_admin' => true]);

        $this->actingAs($admin)
            ->get(route('backups.download', $backup))
            ->assertOk();
    }

    public function test_force_https_redirects_when_enabled(): void
    {
        config(['pluto.security.force_https' => true]);

        $location = $this->get('http://localhost/')->headers->get('Location');

        $this->assertNotNull($location);
        $this->assertStringStartsWith('https://localhost', $location);
    }

    public function test_security_headers_are_present(): void
    {
        \App\Models\Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);

        $response = $this->get('/tehran');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_schedule_includes_typed_backup_commands_twice_daily(): void
    {
        $events = collect(Schedule::events())->map(fn ($event) => $event->command ?? $event->description ?? '');

        $databaseRuns = $events->filter(fn ($cmd) => is_string($cmd) && str_contains($cmd, 'pluto:backup') && str_contains($cmd, 'database'));
        $filesRuns = $events->filter(fn ($cmd) => is_string($cmd) && str_contains($cmd, 'pluto:backup') && str_contains($cmd, 'files'));

        $this->assertGreaterThanOrEqual(2, $databaseRuns->count());
        $this->assertGreaterThanOrEqual(2, $filesRuns->count());
        $this->assertTrue($events->contains(fn ($cmd) => is_string($cmd) && str_contains($cmd, 'backup:clean')));
    }

    public function test_system_errors_page_is_super_admin_only(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);
        $user = User::factory()->create(['is_super_admin' => false]);

        $this->actingAs($admin);
        $this->assertTrue(SystemErrors::canAccess());

        $this->actingAs($user);
        $this->assertFalse(SystemErrors::canAccess());
    }

    public function test_system_error_reader_returns_failed_backups_and_log_errors(): void
    {
        BackupRecord::factory()->failed()->files()->create([
            'message' => 'permission denied',
            'finished_at' => now(),
        ]);

        $logPath = storage_path('logs/laravel.log');
        File::ensureDirectoryExists(dirname($logPath));
        File::put($logPath, "[2026-11-10 12:00:00] testing.ERROR: boom happened\n");

        $reader = app(SystemErrorReader::class);

        $this->assertSame('permission denied', $reader->recentFailedBackups()->first()->message);
        $this->assertNotEmpty($reader->recentLogErrors(5));
        $this->assertSame('ERROR', $reader->recentLogErrors(5)[0]['level']);
    }

    public function test_run_now_rejects_invalid_type(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);

        $this->expectException(\Exception::class);
        app(BackupRecorder::class)->runNow($admin, 'invalid');
    }

    public function test_restore_guide_command_outputs_steps(): void
    {
        $this->artisan('pluto:restore-guide')
            ->expectsOutputToContain('php artisan down')
            ->expectsOutputToContain('php artisan up')
            ->assertSuccessful();
    }

    public function test_run_now_rejects_non_super_admin(): void
    {
        $user = User::factory()->create(['is_super_admin' => false]);

        $this->expectException(\Exception::class);
        app(BackupRecorder::class)->runNow($user);
    }
}
