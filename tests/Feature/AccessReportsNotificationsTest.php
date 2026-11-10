<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Filament\Pages\ManageNotifications;
use App\Filament\Pages\ReportsOverview;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Article;
use App\Models\Category;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Access\SyncUserSiteRoles;
use App\Services\Dashboard\ReportsService;
use App\Services\Notifications\BroadcastSiteNotification;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessReportsNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_sync_user_site_roles_assigns_and_removes_per_site(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);
        $actor = User::factory()->create(['is_super_admin' => true]);
        $target = User::factory()->create();

        app(SyncUserSiteRoles::class)->handle($actor, $target, [
            ['site_id' => $tehran->id, 'role' => 'editor_in_chief', 'is_primary' => true],
            ['site_id' => $fars->id, 'role' => 'journalist', 'is_primary' => false],
        ]);

        $target = $target->fresh();
        $this->assertTrue($target->belongsToSite($tehran->id));
        $this->assertTrue($target->belongsToSite($fars->id));

        setPermissionsTeamId($tehran->id);
        $this->assertTrue($target->fresh()->hasRole('editor_in_chief'));
        setPermissionsTeamId($fars->id);
        $this->assertTrue($target->fresh()->hasRole('journalist'));

        app(SyncUserSiteRoles::class)->handle($actor, $target->fresh(), [
            ['site_id' => $tehran->id, 'role' => 'regional_admin', 'is_primary' => true],
        ]);

        $target = $target->fresh();
        $this->assertTrue($target->belongsToSite($tehran->id));
        $this->assertFalse($target->belongsToSite($fars->id));
        setPermissionsTeamId($tehran->id);
        $this->assertTrue($target->fresh()->hasRole('regional_admin'));
    }

    public function test_edit_user_form_loads_and_saves_site_roles(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran']);
        $actor = User::factory()->create(['is_super_admin' => true]);
        $target = User::factory()->create();
        app(AssignSiteRole::class)->handle($actor, $target, $site->id, 'journalist');

        $this->actingAs($actor);

        $component = Livewire::test(EditUser::class, ['record' => $target->getKey()]);
        $loaded = collect($component->get('data.site_roles') ?? [])->values();
        $this->assertCount(1, $loaded);
        $this->assertSame($site->id, (int) $loaded[0]['site_id']);
        $this->assertSame('journalist', $loaded[0]['role']);

        $component
            ->fillForm([
                'name' => $target->name,
                'mobile' => $target->mobile,
                'site_roles' => [
                    ['site_id' => $site->id, 'role' => 'editor_in_chief', 'is_primary' => true],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        setPermissionsTeamId($site->id);
        $this->assertTrue($target->fresh()->hasRole('editor_in_chief'));
    }

    public function test_reports_service_scopes_to_authorized_sites_and_aggregates_forms(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);
        $category = Category::factory()->create(['site_id' => $tehran->id, 'name' => 'سیاسی']);

        Article::factory()->create([
            'site_id' => $tehran->id,
            'category_id' => $category->id,
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);
        Article::factory()->create([
            'site_id' => $fars->id,
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $form = Form::factory()->create(['site_id' => $tehran->id]);
        FormSubmission::factory()->create(['site_id' => $tehran->id, 'form_id' => $form->id]);

        $user = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $user,
            $tehran->id,
            'editor_in_chief'
        );

        $overview = app(ReportsService::class)->overview($user->fresh());
        $this->assertSame(1, $overview['articles']['total']);
        $this->assertSame(1, $overview['form_submissions']);

        $tops = app(ReportsService::class)->topCategories($user->fresh());
        $this->assertSame('سیاسی', $tops->first()->name);
    }

    public function test_reports_page_accessible_with_permission(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran']);
        $user = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $user,
            $site->id,
            'editor_in_chief'
        );

        $this->actingAs($user->fresh());
        $this->assertTrue(ReportsOverview::canAccess());

        Livewire::test(ReportsOverview::class)->assertSuccessful();
    }

    public function test_broadcast_notification_requires_manage_permission(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran']);
        $admin = User::factory()->create(['is_super_admin' => true]);
        $editor = User::factory()->create();
        $journalist = User::factory()->create();

        app(AssignSiteRole::class)->handle($admin, $editor, $site->id, 'editor_in_chief');
        app(AssignSiteRole::class)->handle($admin, $journalist, $site->id, 'journalist');

        NotificationFacade::fake();

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        app(BroadcastSiteNotification::class)->handle($editor->fresh(), $site->id, 'عنوان', 'متن');
    }

    public function test_regional_admin_can_broadcast_and_open_notifications_page(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran']);
        $super = User::factory()->create(['is_super_admin' => true]);
        $admin = User::factory()->create();
        $member = User::factory()->create();

        app(AssignSiteRole::class)->handle($super, $admin, $site->id, 'regional_admin');
        app(AssignSiteRole::class)->handle($super, $member, $site->id, 'journalist');

        NotificationFacade::fake();
        $count = app(BroadcastSiteNotification::class)->handle($admin->fresh(), $site->id, 'اطلاعیه', 'متن آزمایشی');
        $this->assertGreaterThanOrEqual(2, $count);

        $this->actingAs($admin->fresh());
        $this->assertTrue(ManageNotifications::canAccess());
        Livewire::test(ManageNotifications::class)->assertSuccessful();
    }

    public function test_role_resource_syncs_template_permissions_to_team_copies(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran']);
        $super = User::factory()->create(['is_super_admin' => true]);
        $user = User::factory()->create();
        app(AssignSiteRole::class)->handle($super, $user, $site->id, 'journalist');

        $template = Role::query()->where('name', 'journalist')->whereNull('team_id')->firstOrFail();
        $template->syncPermissions(['articles.view', 'articles.create', 'reports.view']);

        Role::query()
            ->where('name', 'journalist')
            ->where('id', '!=', $template->id)
            ->each(fn (Role $role) => $role->syncPermissions(['articles.view', 'articles.create', 'reports.view']));

        $this->actingAs($super);
        $this->assertTrue(RoleResource::canViewAny());
        $this->assertTrue(RoleResource::canEdit($template));

        setPermissionsTeamId($site->id);
        $this->assertTrue($user->fresh()->hasSitePermission('reports.view', $site->id));
    }
}
