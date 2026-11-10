<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\PageStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Form;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoSyndicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_rss_lists_only_own_published_articles(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $fars = Site::factory()->regional()->create(['slug' => 'fars', 'is_active' => true]);

        Article::factory()->published()->create([
            'site_id' => $tehran->id,
            'title' => 'خبر تهران RSS',
            'slug' => 'tehran-rss',
        ]);
        Article::factory()->published()->create([
            'site_id' => $fars->id,
            'title' => 'خبر فارس RSS',
            'slug' => 'fars-rss',
        ]);
        Article::factory()->create([
            'site_id' => $tehran->id,
            'title' => 'پیش‌نویس',
            'slug' => 'draft-rss',
            'status' => ArticleStatus::Draft,
        ]);

        $response = $this->get('/tehran/rss.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $response->assertSee('خبر تهران RSS', false);
        $response->assertDontSee('خبر فارس RSS', false);
        $response->assertDontSee('پیش‌نویس', false);
        $response->assertSee(route('public.article', ['site' => 'tehran', 'slug' => 'tehran-rss']), false);
    }

    public function test_site_sitemap_includes_published_content_only(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $category = Category::factory()->create([
            'site_id' => $site->id,
            'slug' => 'economy',
            'is_active' => true,
        ]);
        Article::factory()->published()->create([
            'site_id' => $site->id,
            'category_id' => $category->id,
            'slug' => 'eco-1',
            'title' => 'اقتصاد یک',
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'slug' => 'about',
            'status' => PageStatus::Published,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'slug' => 'draft-page',
            'status' => PageStatus::Draft,
        ]);
        Form::factory()->create([
            'site_id' => $site->id,
            'slug' => 'contact',
            'is_active' => true,
        ]);
        Form::factory()->create([
            'site_id' => $site->id,
            'slug' => 'old-form',
            'is_active' => false,
        ]);

        $response = $this->get('/tehran/sitemap.xml');

        $response->assertOk();
        $response->assertSee(route('public.article', ['site' => 'tehran', 'slug' => 'eco-1']), false);
        $response->assertSee(route('public.category', ['site' => 'tehran', 'slug' => 'economy']), false);
        $response->assertSee(route('public.page', ['site' => 'tehran', 'slug' => 'about']), false);
        $response->assertSee(route('public.form', ['site' => 'tehran', 'slug' => 'contact']), false);
        $response->assertDontSee('/tehran/page/draft-page', false);
        $response->assertDontSee('/tehran/forms/old-form', false);
    }

    public function test_sitemap_index_and_robots_list_active_sites(): void
    {
        Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        Site::factory()->regional()->create(['slug' => 'fars', 'is_active' => true]);
        Site::factory()->regional()->create(['slug' => 'dormant', 'is_active' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('public.sitemap', ['site' => 'tehran']), false)
            ->assertSee(route('public.sitemap', ['site' => 'fars']), false)
            ->assertDontSee('/dormant/sitemap.xml', false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee(route('public.sitemap.index'), false)
            ->assertSee(route('public.sitemap', ['site' => 'tehran']), false)
            ->assertDontSee('/dormant/sitemap.xml', false);
    }

    public function test_inactive_site_feed_is_not_found(): void
    {
        Site::factory()->regional()->create(['slug' => 'dormant', 'is_active' => false]);

        $this->get('/dormant/rss.xml')->assertNotFound();
        $this->get('/dormant/sitemap.xml')->assertNotFound();
    }
}
