<?php

namespace App\Filament\Pages;

use App\Services\Dashboard\AdminDashboardService;
use App\Support\PersianDate;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = 'داشبورد';

    protected static ?string $title = 'داشبورد';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    protected static ?int $navigationSort = -2;

    public string $visitsPeriod = '30';

    public ?int $selectedNewsId = null;

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getTitle(): string|Htmlable
    {
        return 'داشبورد';
    }

    public function openNews(int $id): void
    {
        $this->selectedNewsId = $id;
    }

    public function closeNews(): void
    {
        $this->selectedNewsId = null;
    }

    public function updatedVisitsPeriod(): void
    {
        // Livewire reactivity for chart period.
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardData(): array
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $service = app(AdminDashboardService::class);
        $days = match ($this->visitsPeriod) {
            '7' => 7,
            '365' => 365,
            default => 30,
        };

        $queue = $service->newsQueue($user);
        $selected = null;
        if ($this->selectedNewsId) {
            $selected = collect($queue['today']['items'])
                ->concat($queue['tomorrow']['items'])
                ->firstWhere('id', $this->selectedNewsId);
        }

        return [
            'userName' => $user->name,
            'nowLabel' => PersianDate::format(now(), 'l j F Y').'　◷ '.PersianDate::persianDigits(now()->format('H:i')),
            'stats' => $service->headlineStats($user),
            'queue' => $queue,
            'visits' => $service->visitsChart($user, $days),
            'provincial' => $service->provincialSites($user),
            'performance' => $service->performance($user),
            'activities' => $service->recentActivities($user),
            'latestNews' => $service->latestNews($user),
            'quickActions' => $service->quickActions($user),
            'selectedNews' => $selected,
            'unreadNotifications' => $user->unreadNotifications()->count(),
            'sitesUrl' => \App\Filament\Resources\Sites\SiteResource::getUrl('index'),
            'articlesUrl' => \App\Filament\Resources\Articles\ArticleResource::getUrl('index'),
            'notificationsUrl' => ManageNotifications::getUrl(),
        ];
    }
}
