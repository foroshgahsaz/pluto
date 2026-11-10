<?php

namespace Tests\Feature;

use App\Enums\HomepageSectionType;
use App\Enums\MenuItemType;
use App\Enums\PageStatus;
use App\Models\HomepageSection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\Theme;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Settings\SiteSettingsManager;
use App\Support\Tenancy\SiteContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsThemesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_settings_manager_scopes_values_per_site(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);
        $manager = app(SiteSettingsManager::class);

        $manager->set($tehran, 'branding', 'title', 'تهران');
        $manager->set($fars, 'branding', 'title', 'فارس');

        $this->assertSame(['value' => 'تهران'], $manager->get($tehran, 'branding', 'title'));
        $this->assertSame(['value' => 'فارس'], $manager->get($fars, 'branding', 'title'));
        $this->assertNull($manager->get($tehran, 'branding', 'missing'));
    }

    public function test_tehran_user_cannot_manage_fars_menu_or_page(): void
    {
        [$tehran, $fars, $tehranUser] = $this->makeRegionalSetup('regional_admin');

        $farsMenu = Menu::factory()->create(['site_id' => $fars->id]);
        $farsPage = Page::factory()->create(['site_id' => $fars->id]);
        $farsSetting = SiteSetting::factory()->create(['site_id' => $fars->id]);
        $farsSection = HomepageSection::factory()->create(['site_id' => $fars->id]);

        $this->assertFalse($tehranUser->can('view', $farsMenu));
        $this->assertFalse($tehranUser->can('update', $farsMenu));
        $this->assertFalse($tehranUser->can('view', $farsPage));
        $this->assertFalse($tehranUser->can('update', $farsPage));
        $this->assertFalse($tehranUser->can('view', $farsSetting));
        $this->assertFalse($tehranUser->can('view', $farsSection));

        $ownMenu = Menu::factory()->create(['site_id' => $tehran->id]);
        $ownPage = Page::factory()->create(['site_id' => $tehran->id]);

        $this->assertTrue($tehranUser->can('view', $ownMenu));
        $this->assertTrue($tehranUser->can('update', $ownPage));
    }

    public function test_site_scope_isolates_menus_and_pages(): void
    {
        [$tehran, $fars] = $this->makeRegionalSetup('regional_admin');

        Menu::factory()->create(['site_id' => $tehran->id, 'name' => 'Tehran Menu', 'location' => 'primary']);
        Menu::factory()->create(['site_id' => $fars->id, 'name' => 'Fars Menu', 'location' => 'primary']);
        Page::factory()->create(['site_id' => $tehran->id, 'title' => 'Tehran Page']);
        Page::factory()->create(['site_id' => $fars->id, 'title' => 'Fars Page']);

        app(SiteContext::class)->set($tehran);

        $this->assertContains('Tehran Menu', Menu::query()->pluck('name')->all());
        $this->assertNotContains('Fars Menu', Menu::query()->pluck('name')->all());
        $this->assertContains('Tehran Page', Page::query()->pluck('title')->all());
        $this->assertNotContains('Fars Page', Page::query()->pluck('title')->all());
    }

    public function test_menu_items_and_homepage_sections_store_order(): void
    {
        $site = Site::factory()->regional()->create();
        $menu = Menu::factory()->create(['site_id' => $site->id]);

        $first = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'site_id' => $site->id,
            'title' => 'اول',
            'type' => MenuItemType::Url,
            'sort_order' => 1,
        ]);
        $second = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'site_id' => $site->id,
            'title' => 'دوم',
            'type' => MenuItemType::Url,
            'sort_order' => 2,
        ]);

        $ordered = $menu->allItems()->pluck('title')->all();
        $this->assertSame(['اول', 'دوم'], $ordered);

        HomepageSection::factory()->create([
            'site_id' => $site->id,
            'key' => 'latest',
            'type' => HomepageSectionType::Latest,
            'sort_order' => 1,
        ]);
        HomepageSection::factory()->create([
            'site_id' => $site->id,
            'key' => 'featured',
            'type' => HomepageSectionType::Featured,
            'sort_order' => 2,
        ]);

        $keys = $site->homepageSections()->pluck('key')->all();
        $this->assertSame(['latest', 'featured'], $keys);
        $this->assertTrue($first->is_active);
        $this->assertSame(PageStatus::Draft, Page::factory()->create(['site_id' => $site->id])->status);
        $this->assertNotNull($second->id);
    }

    public function test_site_can_be_assigned_a_theme(): void
    {
        $theme = Theme::factory()->create(['key' => 'news-classic', 'name' => 'کلاسیک خبری']);
        $site = Site::factory()->regional()->create(['theme_id' => $theme->id]);

        $this->assertTrue($site->theme->is($theme));
        $this->assertTrue($theme->sites()->whereKey($site->id)->exists());
    }

    public function test_seeder_creates_default_theme_settings_and_menus(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertDatabaseHas('themes', ['key' => 'default']);
        $central = Site::query()->where('slug', config('pluto.seed.central_site_slug'))->firstOrFail();
        $this->assertNotNull($central->theme_id);
        $this->assertDatabaseHas('menus', ['site_id' => $central->id, 'location' => 'primary']);
        $this->assertDatabaseHas('homepage_sections', ['site_id' => $central->id, 'key' => 'latest']);
        $this->assertDatabaseHas('site_settings', ['site_id' => $central->id, 'group' => 'branding', 'key' => 'title']);
    }

    /**
     * @return array{0: Site, 1: Site, 2: User}
     */
    protected function makeRegionalSetup(string $role = 'regional_admin'): array
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran', 'name' => 'Tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars', 'name' => 'Fars']);

        $tehranUser = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $tehranUser,
            $tehran->id,
            $role
        );

        return [$tehran, $fars, $tehranUser->fresh()];
    }
}
