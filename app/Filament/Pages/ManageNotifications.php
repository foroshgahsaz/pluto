<?php

namespace App\Filament\Pages;

use App\Models\Site;
use App\Models\User;
use App\Services\Notifications\BroadcastSiteNotification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use UnitEnum;

class ManageNotifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.manage-notifications';

    protected static ?string $navigationLabel = 'پیام‌ها و اعلان‌ها';

    protected static ?string $title = 'پیام‌ها و اعلان‌ها';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'امنیت و عملیات';

    protected static ?int $navigationSort = 60;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->isGlobalOperator()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('notifications.manage', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->notificationsQuery())
            ->columns([
                TextColumn::make('created_at')
                    ->label('زمان')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('data.title')
                    ->label('عنوان')
                    ->state(fn (DatabaseNotification $record): string => (string) data_get($record->data, 'title', '—'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('data', 'like', '%'.$search.'%');
                    }),
                TextColumn::make('data.body')
                    ->label('متن')
                    ->state(fn (DatabaseNotification $record): string => (string) data_get($record->data, 'body', ''))
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('notifiable.name')
                    ->label('گیرنده')
                    ->placeholder('—'),
                TextColumn::make('site')
                    ->label('سایت')
                    ->state(function (DatabaseNotification $record): string {
                        $siteId = data_get($record->data, 'site_id');
                        if (! $siteId) {
                            return '—';
                        }

                        return (string) (Site::query()->find($siteId)?->name ?? '#'.$siteId);
                    }),
                IconColumn::make('read_at')
                    ->label('خوانده‌شده')
                    ->boolean()
                    ->getStateUsing(fn (DatabaseNotification $record): bool => $record->read_at !== null),
            ])
            ->filters([
                SelectFilter::make('read')
                    ->label('وضعیت')
                    ->options([
                        'unread' => 'خوانده‌نشده',
                        'read' => 'خوانده‌شده',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'unread' => $query->whereNull('read_at'),
                            'read' => $query->whereNotNull('read_at'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('markRead')
                    ->label('خوانده شد')
                    ->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (DatabaseNotification $record): bool => $record->read_at === null)
                    ->action(function (DatabaseNotification $record): void {
                        $record->markAsRead();
                        $this->resetTable();
                    }),
                Action::make('delete')
                    ->label('حذف')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (DatabaseNotification $record): void {
                        $record->delete();
                        $this->resetTable();
                    }),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('created_at', 'desc');
    }

    protected function notificationsQuery(): Builder
    {
        /** @var User $user */
        $user = auth()->user();
        $query = DatabaseNotification::query()->latest();

        if ($user->isGlobalOperator()) {
            return $query;
        }

        $siteIds = array_values(array_filter(
            array_map('intval', $user->authorizedSiteIds()),
            fn (int $siteId) => $user->hasSitePermission('notifications.manage', $siteId)
        ));

        if ($siteIds === []) {
            return $query->whereRaw('1 = 0');
        }

        $userIds = User::query()
            ->where(function ($q) use ($siteIds): void {
                $q->where('is_super_admin', true)
                    ->orWhere('is_platform_admin', true)
                    ->orWhereHas('sites', fn ($s) => $s->whereIn('sites.id', $siteIds));
            })
            ->pluck('id');

        return $query
            ->where('notifiable_type', User::class)
            ->whereIn('notifiable_id', $userIds)
            ->where(function (Builder $q) use ($siteIds): void {
                foreach ($siteIds as $siteId) {
                    $q->orWhere('data->site_id', $siteId);
                }
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('broadcast')
                ->label('ارسال اعلان گروهی')
                ->icon(Heroicon::OutlinedMegaphone)
                ->form([
                    Select::make('site_id')
                        ->label('سایت')
                        ->options(fn () => $this->manageableSites()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    TextInput::make('title')
                        ->label('عنوان')
                        ->required()
                        ->maxLength(120),
                    Textarea::make('body')
                        ->label('متن')
                        ->required()
                        ->rows(4)
                        ->maxLength(1000),
                ])
                ->action(function (array $data): void {
                    $count = app(BroadcastSiteNotification::class)->handle(
                        auth()->user(),
                        (int) $data['site_id'],
                        (string) $data['title'],
                        (string) $data['body'],
                    );

                    Notification::make()
                        ->title('اعلان ارسال شد')
                        ->body("برای {$count} کاربر ارسال شد.")
                        ->success()
                        ->send();

                    $this->resetTable();
                }),
            Action::make('markAllMineRead')
                ->label('خواندن همه اعلان‌های من')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('gray')
                ->action(function (): void {
                    auth()->user()?->unreadNotifications->markAsRead();
                    Notification::make()->title('اعلان‌های شما خوانده شد')->success()->send();
                    $this->resetTable();
                }),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Site>
     */
    protected function manageableSites()
    {
        /** @var User $user */
        $user = auth()->user();
        if ($user->isGlobalOperator()) {
            return Site::query()->orderBy('name')->get();
        }

        return Site::query()
            ->whereIn('id', $user->authorizedSiteIds())
            ->orderBy('name')
            ->get()
            ->filter(fn (Site $site) => $user->hasSitePermission('notifications.manage', (int) $site->id))
            ->values();
    }
}
