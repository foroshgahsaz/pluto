<?php

namespace App\Filament\Pages;

use App\Models\BackupRecord;
use App\Services\Backup\BackupRecorder;
use App\Support\Backup\BackupRunContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
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

    /**
     * @return list<\App\Models\BackupRecord>
     */
    public function recentFailures(): array
    {
        return BackupRecord::query()
            ->where('status', 'failed')
            ->latest('finished_at')
            ->limit(5)
            ->get()
            ->all();
    }

    public function scheduleSummary(): string
    {
        $db = implode(' و ', config('pluto.backup.database_times', []));
        $files = implode(' و ', config('pluto.backup.files_times', []));

        return "زمان‌بندی خودکار: دیتابیس روزانه در {$db} — فایل‌ها روزانه در {$files}";
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
                TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => BackupRunContext::label($state ?: BackupRunContext::TYPE_FULL))
                    ->color(fn (?string $state): string => match ($state) {
                        BackupRunContext::TYPE_DATABASE => 'info',
                        BackupRunContext::TYPE_FILES => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('disk')->label('دیسک'),
                TextColumn::make('path')->label('مسیر')->limit(40)->placeholder('—'),
                TextColumn::make('size_bytes')
                    ->label('حجم')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '—'),
                TextColumn::make('triggerer.name')->label('اجراکننده')->placeholder('زمان‌بند'),
                TextColumn::make('finished_at')->label('زمان')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('message')
                    ->label('پیام / خطا')
                    ->wrap()
                    ->limit(80)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'success' => 'موفق',
                        'failed' => 'ناموفق',
                    ]),
                SelectFilter::make('type')
                    ->label('نوع')
                    ->options([
                        BackupRunContext::TYPE_DATABASE => 'دیتابیس',
                        BackupRunContext::TYPE_FILES => 'فایل‌ها',
                        BackupRunContext::TYPE_FULL => 'کامل',
                    ]),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('دانلود')
                    ->url(fn (BackupRecord $record): string => route('backups.download', $record))
                    ->visible(fn (BackupRecord $record): bool => $record->isSuccessful() && $record->fileExists())
                    ->openUrlInNewTab(),
                Action::make('showError')
                    ->label('جزئیات خطا')
                    ->icon(Heroicon::OutlinedExclamationCircle)
                    ->color('danger')
                    ->visible(fn (BackupRecord $record): bool => $record->status === 'failed' && filled($record->message))
                    ->modalHeading('جزئیات خطای پشتیبان‌گیری')
                    ->modalContent(fn (BackupRecord $record) => view('filament.pages.partials.backup-error', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false),
            ])
            ->paginated([10, 25, 50]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->backupAction(
                name: 'runDatabaseBackup',
                label: 'بکاپ دستی دیتابیس',
                type: BackupRunContext::TYPE_DATABASE,
                icon: Heroicon::OutlinedCircleStack,
            ),
            $this->backupAction(
                name: 'runFilesBackup',
                label: 'بکاپ دستی فایل‌ها',
                type: BackupRunContext::TYPE_FILES,
                icon: Heroicon::OutlinedFolder,
            ),
            $this->backupAction(
                name: 'runFullBackup',
                label: 'بکاپ کامل',
                type: BackupRunContext::TYPE_FULL,
                icon: Heroicon::OutlinedArrowPath,
                color: 'gray',
            ),
            Action::make('restoreGuide')
                ->label('راهنمای بازیابی')
                ->icon(Heroicon::OutlinedBookOpen)
                ->color('gray')
                ->modalHeading('بازیابی کنترل‌شده')
                ->modalContent(view('filament.pages.partials.restore-guide'))
                ->modalSubmitAction(false),
        ];
    }

    protected function backupAction(string $name, string $label, string $type, Heroicon $icon, string $color = 'primary'): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->modalHeading($label.'؟')
            ->modalDescription('این عملیات ممکن است چند دقیقه طول بکشد و روی دیسک خصوصی ذخیره می‌شود.')
            ->action(function (BackupRecorder $recorder) use ($type): void {
                $result = $recorder->runNow(auth()->user(), $type);

                if ($result['exit_code'] === 0) {
                    Notification::make()
                        ->title('پشتیبان‌گیری '.BackupRunContext::label($type).' اجرا شد')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('پشتیبان‌گیری '.BackupRunContext::label($type).' با خطا پایان یافت')
                        ->body(str($result['output'])->limit(300))
                        ->danger()
                        ->send();
                }

                $this->resetTable();
            });
    }
}
