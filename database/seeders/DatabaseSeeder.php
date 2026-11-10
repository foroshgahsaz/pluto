<?php

namespace Database\Seeders;

use App\Enums\HomepageSectionType;
use App\Enums\SiteType;
use App\Models\HomepageSection;
use App\Models\Menu;
use App\Models\Site;
use App\Models\Theme;
use App\Models\User;
use App\Services\Settings\SiteSettingsManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $theme = Theme::query()->firstOrCreate(
            ['key' => 'default'],
            [
                'name' => 'قالب پیش‌فرض',
                'is_active' => true,
                'config_schema_json' => [
                    'colors' => ['primary', 'secondary', 'accent'],
                    'branding' => ['logo', 'title', 'tagline'],
                ],
            ],
        );
        Theme::query()->firstOrCreate(
            ['key' => 'ink-daily'],
            [
                'name' => 'روزنامه مرکب',
                'is_active' => true,
                'config_schema_json' => [
                    'colors' => ['primary', 'secondary', 'accent'],
                    'branding' => ['logo', 'title', 'tagline'],
                    'layout' => ['dense_list', 'hero_kicker'],
                ],
            ],
        );
        Theme::query()->firstOrCreate(
            ['key' => 'field-report'],
            [
                'name' => 'گزارش میدانی',
                'is_active' => true,
                'config_schema_json' => [
                    'colors' => ['primary', 'secondary', 'accent'],
                    'branding' => ['logo', 'title', 'tagline'],
                    'layout' => ['photo_led', 'section_rails'],
                ],
            ],
        );

        $central = Site::query()->firstOrCreate(
            ['slug' => config('pluto.seed.central_site_slug')],
            [
                'type' => SiteType::Central,
                'name' => config('pluto.seed.central_site_name'),
                'locale' => 'fa',
                'timezone' => 'Asia/Tehran',
                'is_active' => true,
                'theme_id' => $theme->id,
            ],
        );

        if (! $central->theme_id) {
            $central->forceFill(['theme_id' => $theme->id])->save();
        }

        $settings = app(SiteSettingsManager::class);
        $settings->syncGroup($central, 'branding', [
            'title' => ['value' => $central->name],
            'tagline' => ['value' => 'سامانه خبری پلوتون'],
        ]);
        $settings->syncGroup($central, 'colors', [
            'primary' => ['value' => '#0f766e'],
            'secondary' => ['value' => '#134e4a'],
            'accent' => ['value' => '#f59e0b'],
        ]);
        $settings->syncGroup($central, 'seo', [
            'meta_title' => ['value' => $central->name],
            'meta_description' => ['value' => 'خبرگزاری چندسایته'],
        ]);

        Menu::query()->firstOrCreate(
            ['site_id' => $central->id, 'location' => 'primary'],
            ['name' => 'منوی اصلی'],
        );
        Menu::query()->firstOrCreate(
            ['site_id' => $central->id, 'location' => 'secondary'],
            ['name' => 'منوی فرعی'],
        );

        HomepageSection::query()->firstOrCreate(
            ['site_id' => $central->id, 'key' => 'latest'],
            [
                'title' => 'آخرین اخبار',
                'type' => HomepageSectionType::Latest,
                'config_json' => ['limit' => 10],
                'sort_order' => 1,
                'is_active' => true,
            ],
        );
        HomepageSection::query()->firstOrCreate(
            ['site_id' => $central->id, 'key' => 'featured'],
            [
                'title' => 'اخبار ویژه',
                'type' => HomepageSectionType::Featured,
                'config_json' => ['limit' => 5],
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        $admin = User::query()->firstOrCreate(
            ['mobile' => config('pluto.seed.super_admin_mobile')],
            [
                'name' => config('pluto.seed.super_admin_name'),
                'email' => config('pluto.seed.super_admin_email'),
                'password' => Hash::make((string) config('pluto.seed.super_admin_password')),
                'is_super_admin' => true,
                'is_active' => true,
                'is_blocked' => false,
                'mobile_verified_at' => now(),
                'email_verified_at' => now(),
            ],
        );

        $admin->forceFill([
            'is_super_admin' => true,
            'is_active' => true,
            'is_blocked' => false,
        ])->save();

        $admin->sites()->syncWithoutDetaching([
            $central->id => ['is_primary' => true],
        ]);
    }
}
