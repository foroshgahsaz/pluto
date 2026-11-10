<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\SiteType;
use App\Filament\Pages\Dashboard;
use App\Models\Article;
use App\Models\Site;
use App\Models\SiteDailyMetric;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Analytics\RecordPageView;
use App\Services\Dashboard\AdminDashboardService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_dashboard_page_renders_welcome_queue_and_stats(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $admin = User::factory()->create(['is_super_admin' => true, 'name' => 'حسین رضایی']);

        Article::factory()->create([
            'site_id' => $site->id,
            'title' => 'خبر صف امروز',
            'status' => ArticleStatus::Approved,
            'summary' => 'خلاصه صف',
            'editor_note' => 'یادداشت آزمایشی',
        ]);
        Article::factory()->published()->create([
            'site_id' => $site->id,
            'title' => 'خبر منتشرشده داشبورد',
            'view_count' => 40,
        ]);
        SiteDailyMetric::query()->create([
            'site_id' => $site->id,
            'metric_date' => now()->toDateString(),
            'page_views' => 250,
        ]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->assertSuccessful()
            ->assertSee('سلام، حسین رضایی')
            ->assertSee('صف خبر')
            ->assertSee('خبر صف امروز')
            ->assertSee('تعداد سایت‌های فعال')
            ->assertSee('نمودار بازدیدها')
            ->assertSee('وضعیت سایت‌های استانی')
            ->assertSee('عملکرد کلی سامانه')
            ->assertSee('آخرین فعالیت‌ها')
            ->assertSee('جدیدترین اخبار')
            ->assertSee('مدیریت سریع')
            ->assertSee('خبر منتشرشده داشبورد');
    }

    public function test_news_queue_modal_opens_and_closes(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran']);
        $admin = User::factory()->create(['is_super_admin' => true]);
        $article = Article::factory()->create([
            'site_id' => $site->id,
            'title' => 'جزئیات صف خبر',
            'status' => ArticleStatus::InReview,
            'summary' => 'خلاصه مودال',
        ]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->call('openNews', $article->id)
            ->assertSet('selectedNewsId', $article->id)
            ->assertSee('جزئیات صف خبر')
            ->assertSee('خلاصه مودال')
            ->assertSee('یادداشت دبیر')
            ->call('closeNews')
            ->assertSet('selectedNewsId', null);
    }

    public function test_page_view_recording_updates_metrics(): void
    {
        $site = Site::factory()->create(['type' => SiteType::Regional, 'slug' => 'fars', 'is_active' => true]);
        $article = Article::factory()->published()->create(['site_id' => $site->id, 'view_count' => 0]);

        app(RecordPageView::class)->forArticle($article->fresh());
        app(RecordPageView::class)->forHomepage($site);

        $this->assertSame(1, $article->fresh()->view_count);
        $metric = SiteDailyMetric::query()->where('site_id', $site->id)->first();
        $this->assertNotNull($metric);
        $this->assertTrue($metric->metric_date->isSameDay(now()));
        $this->assertGreaterThanOrEqual(2, (int) $metric->page_views);
    }

    public function test_dashboard_service_scopes_to_authorized_sites(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $fars = Site::factory()->regional()->create(['slug' => 'fars', 'is_active' => true]);
        Article::factory()->published()->count(2)->create(['site_id' => $tehran->id]);
        Article::factory()->published()->count(5)->create(['site_id' => $fars->id]);

        $user = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $user,
            $tehran->id,
            'editor_in_chief'
        );

        $stats = app(AdminDashboardService::class)->headlineStats($user->fresh());
        $this->assertSame(2, $stats['published']);
        $this->assertSame(1, $stats['active_sites']);
    }
}
