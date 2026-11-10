<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Editorial\PublicArticleQuery;
use App\Support\Tenancy\SiteContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_tehran_user_cannot_view_edit_or_delete_fars_article(): void
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

        $farsArticle = Article::factory()->published()->create(['site_id' => $fars->id]);

        $this->assertFalse($tehranUser->fresh()->can('view', $farsArticle));
        $this->assertFalse($tehranUser->fresh()->can('update', $farsArticle));
        $this->assertFalse($tehranUser->fresh()->can('delete', $farsArticle));
        $this->assertFalse($tehranUser->fresh()->can('publish', $farsArticle));
    }

    public function test_public_query_respects_site_context(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);

        Article::factory()->published()->create(['site_id' => $tehran->id, 'title' => 'Tehran News']);
        Article::factory()->published()->create(['site_id' => $fars->id, 'title' => 'Fars News']);

        app(SiteContext::class)->set($tehran);
        $titles = app(PublicArticleQuery::class)->latest(10)->pluck('title')->all();

        $this->assertContains('Tehran News', $titles);
        $this->assertNotContains('Fars News', $titles);
    }

    public function test_site_scope_hides_other_site_articles(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);

        Article::factory()->create(['site_id' => $tehran->id, 'title' => 'T']);
        Article::factory()->create(['site_id' => $fars->id, 'title' => 'F']);

        app(SiteContext::class)->set($tehran);

        $this->assertSame(['T'], Article::query()->pluck('title')->all());
    }
}
