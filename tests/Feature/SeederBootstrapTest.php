<?php

namespace Tests\Feature;

use App\Enums\SiteType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_central_site_and_super_admin(): void
    {
        $this->seed();

        $site = Site::query()->where('slug', 'central')->first();
        $admin = User::query()->where('mobile', config('pluto.seed.super_admin_mobile'))->first();

        $this->assertNotNull($site);
        $this->assertSame(SiteType::Central, $site->type);
        $this->assertNotNull($admin);
        $this->assertTrue($admin->is_super_admin);
        $this->assertTrue($admin->sites->contains($site));
    }
}
