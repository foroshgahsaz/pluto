<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Auth\LoginAuditor;
use App\Services\Dashboard\EditorialDashboardService;
use App\Services\Editorial\TransitionArticleStatus;
use App\Services\Notifications\SiteNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

class DashboardNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_dashboard_counts_only_authorized_sites(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);

        Article::factory()->create(['site_id' => $tehran->id, 'status' => ArticleStatus::Published, 'published_at' => now()]);
        Article::factory()->create(['site_id' => $tehran->id, 'status' => ArticleStatus::Draft]);
        Article::factory()->count(3)->create(['site_id' => $fars->id, 'status' => ArticleStatus::Published, 'published_at' => now()]);

        $tehranUser = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $tehranUser,
            $tehran->id,
            'editor_in_chief'
        );

        $counts = app(EditorialDashboardService::class)->articleCounts($tehranUser->fresh());

        $this->assertSame(2, $counts['total']);
        $this->assertSame(1, $counts['published']);
        $this->assertSame(1, $counts['draft']);
        $this->assertSame(0, $counts['in_review']);
    }

    public function test_submit_for_review_notifies_editors_not_journalist(): void
    {
        [$site, $editor, $journalist] = $this->makeEditorialUsers();
        $article = Article::factory()->create([
            'site_id' => $site->id,
            'status' => ArticleStatus::Draft,
            'created_by' => $journalist->id,
        ]);

        NotificationFacade::fake();
        app(TransitionArticleStatus::class)->submit($journalist, $article);

        NotificationFacade::assertSentTo($editor, \Filament\Notifications\DatabaseNotification::class);
        NotificationFacade::assertNotSentTo($journalist, \Filament\Notifications\DatabaseNotification::class);
    }

    public function test_reject_notifies_article_creator(): void
    {
        [$site, $editor, $journalist] = $this->makeEditorialUsers();
        $article = Article::factory()->create([
            'site_id' => $site->id,
            'status' => ArticleStatus::Draft,
            'created_by' => $journalist->id,
        ]);

        $transitions = app(TransitionArticleStatus::class);
        $transitions->submit($journalist, $article);

        NotificationFacade::fake();
        $transitions->reject($editor, $article->fresh(), 'نیاز به اصلاح');

        NotificationFacade::assertSentTo($journalist, \Filament\Notifications\DatabaseNotification::class);
    }

    public function test_role_assignment_notifies_target_user(): void
    {
        $site = Site::factory()->regional()->create();
        $target = User::factory()->create();
        $actor = User::factory()->create(['is_super_admin' => true]);

        NotificationFacade::fake();
        app(AssignSiteRole::class)->handle($actor, $target, $site->id, 'journalist');

        NotificationFacade::assertSentTo($target, \Filament\Notifications\DatabaseNotification::class);
    }

    public function test_failed_login_threshold_notifies_super_admin(): void
    {
        config([
            'pluto.security.failed_login_threshold' => 3,
            'pluto.security.failed_login_window_minutes' => 15,
        ]);

        NotificationFacade::fake();

        $admin = User::factory()->create(['is_super_admin' => true, 'is_active' => true]);
        $auditor = app(LoginAuditor::class);

        $auditor->record('login_failed', method: 'password', mobile: '09121112233');
        $auditor->record('login_failed', method: 'password', mobile: '09121112233');
        NotificationFacade::assertNotSentTo($admin, \Filament\Notifications\DatabaseNotification::class);

        $auditor->record('login_failed', method: 'password', mobile: '09121112233');
        NotificationFacade::assertSentTo($admin, \Filament\Notifications\DatabaseNotification::class);
    }

    public function test_publish_volume_series_length_matches_window(): void
    {
        $site = Site::factory()->regional()->create();
        $user = User::factory()->create(['is_super_admin' => true]);

        Article::factory()->create([
            'site_id' => $site->id,
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDays(1),
        ]);

        $volume = app(EditorialDashboardService::class)->publishVolumeByDay($user, 7);

        $this->assertCount(7, $volume['labels']);
        $this->assertCount(7, $volume['data']);
        $this->assertSame(1, array_sum($volume['data']));
    }

    public function test_users_with_site_permission_resolver(): void
    {
        [$site, $editor, $journalist] = $this->makeEditorialUsers();

        $approvers = app(SiteNotificationService::class)
            ->usersWithSitePermission($site->id, 'articles.approve');

        $this->assertTrue($approvers->contains(fn (User $u) => $u->is($editor)));
        $this->assertFalse($approvers->contains(fn (User $u) => $u->is($journalist)));
    }

    /**
     * @return array{0: Site, 1: User, 2: User}
     */
    protected function makeEditorialUsers(): array
    {
        $site = Site::factory()->regional()->create();
        $assigner = User::factory()->create(['is_super_admin' => true]);

        $editor = User::factory()->create();
        app(AssignSiteRole::class)->handle($assigner, $editor, $site->id, 'editor_in_chief');

        $journalist = User::factory()->create();
        app(AssignSiteRole::class)->handle($assigner, $journalist, $site->id, 'journalist');

        return [$site, $editor->fresh(), $journalist->fresh()];
    }
}
