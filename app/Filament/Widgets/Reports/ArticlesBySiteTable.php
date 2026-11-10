<?php

namespace App\Filament\Widgets\Reports;

use App\Filament\Widgets\ArticleStatsOverview;
use App\Models\User;
use App\Services\Dashboard\ReportsService;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class ArticlesBySiteTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'آمار اخبار به تفکیک سایت';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ArticleStatsOverview::canView();
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => $this->rows())
            ->columns([
                TextColumn::make('site_name')->label('سایت'),
                TextColumn::make('total')->label('کل'),
                TextColumn::make('published')->label('منتشر'),
                TextColumn::make('draft')->label('پیش‌نویس'),
            ])
            ->paginated(false);
    }

    /**
     * @return Collection<string, array{site_id:int, site_name:string, published:int, draft:int, total:int}>
     */
    protected function rows(): Collection
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return collect();
        }

        $siteId = filled($this->pageFilters['site_id'] ?? null) ? (int) $this->pageFilters['site_id'] : null;
        $from = filled($this->pageFilters['from'] ?? null) ? Carbon::parse($this->pageFilters['from'])->startOfDay() : null;
        $to = filled($this->pageFilters['to'] ?? null) ? Carbon::parse($this->pageFilters['to'])->endOfDay() : null;

        return app(ReportsService::class)
            ->articlesBySite($user, $siteId, $from, $to)
            ->mapWithKeys(fn (object $row): array => [
                (string) $row->site_id => [
                    'site_id' => (int) $row->site_id,
                    'site_name' => (string) $row->site_name,
                    'published' => (int) $row->published,
                    'draft' => (int) $row->draft,
                    'total' => (int) $row->total,
                ],
            ]);
    }
}
