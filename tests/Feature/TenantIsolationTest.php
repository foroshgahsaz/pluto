<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Support\Tenancy\SiteContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_tehran_user_cannot_view_fars_category_via_policy(): void
    {
        [$tehran, $fars, $tehranUser] = $this->makeRegionalSetup();

        $farsCategory = Category::factory()->create(['site_id' => $fars->id, 'name' => 'Fars Cat']);

        $this->assertFalse($tehranUser->can('view', $farsCategory));
        $this->assertFalse($tehranUser->can('update', $farsCategory));
        $this->assertFalse($tehranUser->can('delete', $farsCategory));
    }

    public function test_tehran_user_can_manage_own_site_category(): void
    {
        [$tehran, $fars, $tehranUser] = $this->makeRegionalSetup();

        $tehranCategory = Category::factory()->create(['site_id' => $tehran->id]);

        $this->assertTrue($tehranUser->can('view', $tehranCategory));
        $this->assertTrue($tehranUser->can('update', $tehranCategory));
    }

    public function test_site_scope_isolates_queries_when_context_set(): void
    {
        [$tehran, $fars] = $this->makeRegionalSetup();

        Category::factory()->create(['site_id' => $tehran->id, 'name' => 'T1']);
        Category::factory()->create(['site_id' => $fars->id, 'name' => 'F1']);

        app(SiteContext::class)->set($tehran);

        $names = Category::query()->pluck('name')->all();

        $this->assertContains('T1', $names);
        $this->assertNotContains('F1', $names);
    }

    public function test_direct_model_lookup_still_requires_authorization_for_cross_site(): void
    {
        [$tehran, $fars, $tehranUser] = $this->makeRegionalSetup();
        $farsCategory = Category::factory()->create(['site_id' => $fars->id]);

        $this->actingAs($tehranUser);

        $this->assertFalse($tehranUser->can('view', Category::query()->withoutGlobalScopes()->findOrFail($farsCategory->id)));
    }

    public function test_user_can_have_different_roles_per_site(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran', 'name' => 'Tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars', 'name' => 'Fars']);
        $user = User::factory()->create();

        $assigner = User::factory()->create(['is_super_admin' => true]);
        $service = app(AssignSiteRole::class);

        $service->handle($assigner, $user, $tehran->id, 'regional_admin');
        $service->handle($assigner, $user, $fars->id, 'journalist');

        setPermissionsTeamId($tehran->id);
        $this->assertTrue($user->fresh()->hasRole('regional_admin'));

        setPermissionsTeamId($fars->id);
        $this->assertTrue($user->fresh()->hasRole('journalist'));
        $this->assertFalse($user->fresh()->hasRole('regional_admin'));
    }

    /**
     * @return array{0: Site, 1: Site, 2: User}
     */
    protected function makeRegionalSetup(): array
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran', 'name' => 'Tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars', 'name' => 'Fars']);

        $tehranUser = User::factory()->create();
        $tehranUser->sites()->attach($tehran->id, ['is_primary' => true]);

        setPermissionsTeamId($tehran->id);
        Role::findOrCreate('regional_admin', 'web');
        $tehranUser->assignRole('regional_admin');

        return [$tehran, $fars, $tehranUser->fresh()];
    }
}
