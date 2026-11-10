<?php

namespace Database\Seeders;

use App\Enums\SiteType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $central = Site::query()->firstOrCreate(
            ['slug' => config('pluto.seed.central_site_slug')],
            [
                'type' => SiteType::Central,
                'name' => config('pluto.seed.central_site_name'),
                'locale' => 'fa',
                'timezone' => 'Asia/Tehran',
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
