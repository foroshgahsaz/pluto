<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageBackups;
use App\Models\Article;
use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PlatformAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_platform_admin_sees_all_sites_and_can_publish_but_not_backups(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);
        $article = Article::factory()->create(['site_id' => $fars->id]);

        $chief = User::factory()->create(['is_platform_admin' => true]);

        $this->assertTrue($chief->belongsToSite($tehran->id));
        $this->assertTrue($chief->belongsToSite($fars->id));
        $this->assertContains($tehran->id, $chief->authorizedSiteIds());
        $this->assertContains($fars->id, $chief->authorizedSiteIds());

        $this->assertTrue($chief->hasSitePermission('articles.publish', $fars->id));
        $this->assertTrue($chief->hasSitePermission('users.assign_roles', $tehran->id));
        $this->assertFalse($chief->hasSitePermission('backups.manage', $tehran->id));

        $this->assertTrue(Gate::forUser($chief)->allows('articles.publish'));
        $this->assertTrue(Gate::forUser($chief)->denies('backups.manage'));

        $this->actingAs($chief);
        $this->assertFalse(ManageBackups::canAccess());

        $this->assertTrue($chief->can('update', $article));
    }

    public function test_platform_admin_cannot_edit_developer_accounts(): void
    {
        $dev = User::factory()->create(['is_super_admin' => true]);
        $chief = User::factory()->create(['is_platform_admin' => true]);

        $this->assertFalse($chief->can('update', $dev));
        $this->assertFalse($chief->can('delete', $dev));
        $this->assertTrue($dev->can('update', $chief));
    }

    public function test_regional_admin_stays_site_scoped(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);
        $dev = User::factory()->create(['is_super_admin' => true]);
        $regional = User::factory()->create();

        app(AssignSiteRole::class)->handle($dev, $regional, $tehran->id, 'regional_admin');
        $regional = $regional->fresh();

        $this->assertTrue($regional->belongsToSite($tehran->id));
        $this->assertFalse($regional->belongsToSite($fars->id));
        $this->assertTrue($regional->hasSitePermission('articles.publish', $tehran->id));
        $this->assertFalse($regional->hasSitePermission('articles.publish', $fars->id));
    }

    public function test_developer_still_has_backup_access(): void
    {
        $dev = User::factory()->create(['is_super_admin' => true]);
        $this->assertTrue($dev->hasSitePermission('backups.manage', 1));
        $this->assertTrue(Gate::forUser($dev)->allows('backups.manage'));
        $this->actingAs($dev);
        $this->assertTrue(ManageBackups::canAccess());
    }
}
