<?php

namespace App\Filament\Pages;

use App\Models\BackupRecord;
use App\Services\Backup\BackupRecorder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class ManageBackups extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.manage-backups';

    protected static ?string $navigationLabel = 'پشتیبان‌گیری';

    protected static ?string $title = 'مدیریت پشتیبان‌ها';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static string|UnitEnum|null $navigationGroup = 'امنیت و عملیات';

    protected static ?int $navigationSort = 90;

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BackupRecord::query()->latest())
            ->columns([
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'success' ? 'موفق' : 'ناموفق')
                    ->color(fn (string $state): string => $state === 'success' ? 'success' : 'danger'),
                TextColumn::make('disk')->label('دیسک'),
                TextColumn::make('path')->label('مسیر')->limit(40)->placeholder('—'),
                TextColumn::make('size_bytes')
                    ->label('حجم')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '—'),
                TextColumn::make('triggerer.name')->label('اجراکننده')->placeholder('زمان‌بند'),
                TextColumn::make('finished_at')->label('زمان')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('message')->label('پیام')->limit(40)->toggleable(),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('دانلود')
                    ->url(fn (BackupRecord $record): string => route('backups.download', $record))
                    ->visible(fn (BackupRecord $record): bool => $record->isSuccessful() && $record->fileExists())
                    ->openUrlInNewTab(),
            ])
            ->paginated([10, 25, 50]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runBackup')
                ->label('اجرای پشتیبان‌گیری')
                ->icon(Heroicon::OutlinedArrowPath)
                ->requiresConfirmation()
                ->modalHeading('اجرای پشتیبان‌گیری اکنون؟')
                ->modalDescription('این عملیات ممکن است چند دقیقه طول بکشد و روی دیسک خصوصی ذخیره می‌شود.')
                ->action(function (BackupRecorder $recorder): void {
                    $result = $recorder->runNow(auth()->user());

                    if ($result['exit_code'] === 0) {
                        Notification::make()
                            ->title('پشتیبان‌گیری اجرا شد')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('پشتیبان‌گیری با خطا پایان یافت')
                            ->body(str($result['output'])->limit(300))
                            ->danger()
                            ->send();
                    }

                    $this->resetTable();
                }),
            Action::make('restoreGuide')
                ->label('راهنمای بازیابی')
                ->icon(Heroicon::OutlinedBookOpen)
                ->color('gray')
                ->modalHeading('بازیابی کنترل‌شده')
                ->modalContent(view('filament.pages.partials.restore-guide'))
                ->modalSubmitAction(false),
        ];
    }
}
