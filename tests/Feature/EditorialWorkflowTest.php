<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Exceptions\InvalidArticleTransitionException;
use App\Jobs\PublishScheduledArticlesJob;
use App\Models\Article;
use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Editorial\TransitionArticleStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected TransitionArticleStatus $transitions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->transitions = app(TransitionArticleStatus::class);
    }

    public function test_full_happy_path_draft_to_published(): void
    {
        [$site, $editor, $journalist] = $this->makeEditorialUsers();
        $article = Article::factory()->create(['site_id' => $site->id, 'status' => ArticleStatus::Draft]);

        $this->transitions->submit($journalist, $article);
        $this->assertSame(ArticleStatus::InReview, $article->fresh()->status);

        $this->transitions->approve($editor, $article->fresh());
        $this->assertSame(ArticleStatus::Approved, $article->fresh()->status);

        $this->transitions->publishNow($editor, $article->fresh());
        $published = $article->fresh();

        $this->assertSame(ArticleStatus::Published, $published->status);
        $this->assertNotNull($published->published_at);
        $this->assertGreaterThan(0, $published->revision_count);
    }

    public function test_reject_requires_reason_and_can_return_to_draft(): void
    {
        [$site, $editor, $journalist] = $this->makeEditorialUsers();
        $article = Article::factory()->create(['site_id' => $site->id, 'status' => ArticleStatus::Draft]);

        $this->transitions->submit($journalist, $article);
        $this->transitions->reject($editor, $article->fresh(), 'نیاز به بازنویسی دارد');

        $rejected = $article->fresh();
        $this->assertSame(ArticleStatus::Rejected, $rejected->status);
        $this->assertSame('نیاز به بازنویسی دارد', $rejected->rejection_reason);

        $this->transitions->returnRejectedToDraft($journalist, $rejected);
        $this->assertSame(ArticleStatus::Draft, $article->fresh()->status);
    }

    public function test_invalid_transition_is_blocked(): void
    {
        [$site, $editor] = $this->makeEditorialUsers();
        $article = Article::factory()->create(['site_id' => $site->id, 'status' => ArticleStatus::Draft]);

        $this->expectException(InvalidArticleTransitionException::class);
        $this->transitions->publishNow($editor, $article);
    }

    public function test_user_without_publish_permission_cannot_publish(): void
    {
        [$site, $editor, $journalist] = $this->makeEditorialUsers();
        $article = Article::factory()->create(['site_id' => $site->id, 'status' => ArticleStatus::Draft]);

        $this->transitions->submit($journalist, $article);
        $this->transitions->approve($editor, $article->fresh());

        $this->expectException(AuthorizationException::class);
        $this->transitions->publishNow($journalist, $article->fresh());
    }

    public function test_scheduled_job_publishes_due_articles(): void
    {
        [$site, $editor] = $this->makeEditorialUsers();
        $article = Article::factory()->create([
            'site_id' => $site->id,
            'status' => ArticleStatus::Approved,
        ]);

        $this->transitions->schedule($editor, $article, now()->addMinutes(5));
        $article->fresh()->forceFill([
            'scheduled_for' => now()->subMinute(),
            'status' => ArticleStatus::Scheduled,
        ])->save();

        (new PublishScheduledArticlesJob)->handle(app(TransitionArticleStatus::class), app(\App\Support\Tenancy\SiteContext::class));

        $this->assertSame(ArticleStatus::Published, $article->fresh()->status);
        $this->assertNotNull($article->fresh()->published_at);
    }

    public function test_tehran_user_cannot_transition_fars_article(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);

        $tehranEditor = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $tehranEditor,
            $tehran->id,
            'editor_in_chief'
        );

        $farsArticle = Article::factory()->create([
            'site_id' => $fars->id,
            'status' => ArticleStatus::InReview,
        ]);

        $this->expectException(AuthorizationException::class);
        $this->transitions->approve($tehranEditor, $farsArticle);
    }

    /**
     * @return array{0: Site, 1: User, 2: User}
     */
    protected function makeEditorialUsers(): array
    {
        $site = Site::factory()->regional()->create();
        $super = User::factory()->create(['is_super_admin' => true]);
        $editor = User::factory()->create();
        $journalist = User::factory()->create();

        $assign = app(AssignSiteRole::class);
        $assign->handle($super, $editor, $site->id, 'editor_in_chief');
        $assign->handle($super, $journalist, $site->id, 'journalist');

        return [$site, $editor->fresh(), $journalist->fresh()];
    }
}
