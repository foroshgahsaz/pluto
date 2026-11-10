<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Media\MediaUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_tehran_user_cannot_download_fars_private_media(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);

        $tehranUser = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $tehranUser,
            $tehran->id,
            'editor_in_chief'
        );

        $farsPrivate = Media::factory()->private()->withStoredFile()->create(['site_id' => $fars->id]);

        $this->actingAs($tehranUser->fresh())
            ->get(route('media.private.download', $farsPrivate))
            ->assertForbidden();
    }

    public function test_same_site_user_can_download_private_media(): void
    {
        $site = Site::factory()->regional()->create();
        $user = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $user,
            $site->id,
            'editor_in_chief'
        );

        $media = Media::factory()->private()->withStoredFile()->create(['site_id' => $site->id]);

        $this->actingAs($user->fresh())
            ->get(route('media.private.download', $media))
            ->assertOk();
    }

    public function test_upload_rejects_php_extension(): void
    {
        $site = Site::factory()->regional()->create();
        $file = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');

        $this->expectException(ValidationException::class);
        app(MediaUploadService::class)->store($file, ['site_id' => $site->id]);
    }

    public function test_upload_stores_public_image_on_public_disk(): void
    {
        $site = Site::factory()->regional()->create();
        $file = UploadedFile::fake()->image('news.jpg', 100, 100);

        $media = app(MediaUploadService::class)->store($file, [
            'site_id' => $site->id,
            'visibility' => 'public',
            'category' => 'image',
        ], User::factory()->create(['is_super_admin' => true]));

        $this->assertSame('public', $media->disk);
        $this->assertSame('public', $media->visibility);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_upload_stores_private_file_on_private_disk(): void
    {
        $site = Site::factory()->regional()->create();
        $path = sys_get_temp_dir().'/pluto-test-'.uniqid().'.pdf';
        file_put_contents($path, '%PDF-1.4 test document');
        $file = new UploadedFile($path, 'doc.pdf', 'application/pdf', null, true);

        $media = app(MediaUploadService::class)->store($file, [
            'site_id' => $site->id,
            'visibility' => 'private',
        ], User::factory()->create(['is_super_admin' => true]));

        $this->assertSame('local', $media->disk);
        $this->assertTrue($media->isPrivate());
        Storage::disk('local')->assertExists($media->path);
        @unlink($path);
    }

    public function test_folders_are_site_scoped_in_policy(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);
        $user = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $user,
            $tehran->id,
            'editor_in_chief'
        );

        $farsFolder = MediaFolder::factory()->create(['site_id' => $fars->id]);

        $this->assertFalse($user->fresh()->can('view', $farsFolder));
        $this->assertFalse($user->fresh()->can('update', $farsFolder));
    }
}
