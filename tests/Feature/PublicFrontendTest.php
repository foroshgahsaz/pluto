<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\PageStatus;
use App\Enums\SiteType;
use App\Models\Article;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Page;
use App\Models\Site;
use App\Services\Settings\SiteSettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_central_homepage_renders_brand_and_sections(): void
    {
        $central = Site::factory()->central()->create([
            'slug' => 'central',
            'name' => 'سایت مرکزی',
            'is_active' => true,
        ]);
        app(SiteSettingsManager::class)->syncGroup($central, 'branding', [
            'title' => ['value' => 'پلوتون مرکزی'],
            'tagline' => ['value' => 'نبض خبر'],
        ]);
        HomepageSection::factory()->create([
            'site_id' => $central->id,
            'key' => 'latest',
            'title' => 'آخرین اخبار',
            'sort_order' => 1,
        ]);
        Article::factory()->published()->create([
            'site_id' => $central->id,
            'title' => 'خبر مرکزی یک',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('پلوتون مرکزی', false)
            ->assertSee('خبر مرکزی یک', false)
            ->assertSee('آخرین اخبار', false);
    }

    public function test_regional_site_shows_only_own_published_articles(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $fars = Site::factory()->regional()->create(['slug' => 'fars', 'is_active' => true]);

        $tehranArticle = Article::factory()->published()->create([
            'site_id' => $tehran->id,
            'title' => 'خبر تهران',
            'slug' => 'tehran-news',
        ]);
        $farsArticle = Article::factory()->published()->create([
            'site_id' => $fars->id,
            'title' => 'خبر فارس',
            'slug' => 'fars-news',
        ]);
        Article::factory()->create([
            'site_id' => $tehran->id,
            'title' => 'پیش‌نویس تهران',
            'slug' => 'tehran-draft',
            'status' => ArticleStatus::Draft,
        ]);

        $this->get('/tehran/news')
            ->assertOk()
            ->assertSee('خبر تهران', false)
            ->assertDontSee('خبر فارس', false)
            ->assertDontSee('پیش‌نویس تهران', false);

        $this->get('/tehran/news/'.$tehranArticle->slug)
            ->assertOk()
            ->assertSee('خبر تهران', false);

        $this->get('/tehran/news/'.$farsArticle->slug)->assertNotFound();
        $this->get('/tehran/news/tehran-draft')->assertNotFound();
    }

    public function test_inactive_site_is_not_public(): void
    {
        Site::factory()->regional()->create(['slug' => 'dormant', 'is_active' => false]);

        $this->get('/dormant')->assertNotFound();
    }

    public function test_published_page_and_category_routes(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $category = Category::factory()->create([
            'site_id' => $site->id,
            'name' => 'اقتصاد',
            'slug' => 'economy',
            'is_active' => true,
        ]);
        Article::factory()->published()->create([
            'site_id' => $site->id,
            'category_id' => $category->id,
            'title' => 'گزارش اقتصادی',
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'درباره ما',
            'slug' => 'about',
            'status' => PageStatus::Published,
            'body' => '<p>متن درباره</p>',
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'پیش‌نویس',
            'slug' => 'draft-page',
            'status' => PageStatus::Draft,
        ]);

        $this->get('/tehran/category/economy')
            ->assertOk()
            ->assertSee('اقتصاد', false)
            ->assertSee('گزارش اقتصادی', false);

        $this->get('/tehran/page/about')
            ->assertOk()
            ->assertSee('درباره ما', false)
            ->assertSee('متن درباره', false);

        $this->get('/tehran/page/draft-page')->assertNotFound();
    }

    public function test_search_finds_published_articles_in_site(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        Article::factory()->published()->create([
            'site_id' => $site->id,
            'title' => 'بارش برف در تهران',
        ]);

        $this->get('/tehran/search?q='.urlencode('بارش'))
            ->assertOk()
            ->assertSee('بارش برف در تهران', false);
    }

    public function test_central_missing_returns_not_found(): void
    {
        Site::factory()->regional()->create(['slug' => 'tehran', 'type' => SiteType::Regional]);

        $this->get('/')->assertNotFound();
    }
}
