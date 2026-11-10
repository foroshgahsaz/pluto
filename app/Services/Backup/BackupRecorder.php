<?php

namespace App\Services\Backup;

use App\Models\BackupRecord;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

class BackupRecorder
{
    public function recordSuccess(BackupWasSuccessful $event, ?User $triggeredBy = null): BackupRecord
    {
        $newest = BackupDestination::create($event->diskName, $event->backupName)->newestBackup();

        $record = BackupRecord::query()->create([
            'status' => 'success',
            'disk' => $event->diskName,
            'backup_name' => $event->backupName,
            'path' => $newest?->path(),
            'size_bytes' => $newest ? (int) $newest->sizeInBytes() : null,
            'message' => 'پشتیبان‌گیری با موفقیت انجام شد.',
            'triggered_by' => $triggeredBy?->id,
            'finished_at' => now(),
        ]);

        activity('backup')
            ->causedBy($triggeredBy)
            ->performedOn($record)
            ->withProperties([
                'disk' => $record->disk,
                'path' => $record->path,
                'size_bytes' => $record->size_bytes,
            ])
            ->log('backup.succeeded');

        $this->notifySuperAdmins(
            'پشتیبان‌گیری موفق',
            "دیسک {$record->disk}".($record->path ? " — {$record->path}" : ''),
            'success',
        );

        return $record;
    }

    public function recordFailure(BackupHasFailed $event, ?User $triggeredBy = null): BackupRecord
    {
        $record = BackupRecord::query()->create([
            'status' => 'failed',
            'disk' => $event->diskName,
            'backup_name' => $event->backupName,
            'path' => null,
            'size_bytes' => null,
            'message' => $event->exception->getMessage(),
            'triggered_by' => $triggeredBy?->id,
            'finished_at' => now(),
        ]);

        activity('backup')
            ->causedBy($triggeredBy)
            ->performedOn($record)
            ->withProperties([
                'disk' => $record->disk,
                'error' => $record->message,
            ])
            ->log('backup.failed');

        $this->notifySuperAdmins(
            'پشتیبان‌گیری ناموفق',
            $record->message ?: 'خطای ناشناخته',
            'danger',
        );

        return $record;
    }

    /**
     * @return array{exit_code: int, output: string}
     */
    public function runNow(?User $actor = null): array
    {
        if ($actor && ! $actor->isSuperAdmin()) {
            throw new Exception('فقط سوپر ادمین می‌تواند پشتیبان‌گیری را اجرا کند.');
        }

        activity('backup')
            ->causedBy($actor)
            ->withProperties(['command' => 'backup:run'])
            ->log('backup.triggered');

        $exitCode = Artisan::call('backup:run');

        return [
            'exit_code' => $exitCode,
            'output' => Artisan::output(),
        ];
    }

    protected function notifySuperAdmins(string $title, string $body, string $tone): void
    {
        $notification = Notification::make()->title($title)->body($body);
        $notification = match ($tone) {
            'danger' => $notification->danger(),
            default => $notification->success(),
        };

        foreach (User::query()->where('is_super_admin', true)->where('is_active', true)->get() as $admin) {
            $admin->notify(new \Filament\Notifications\DatabaseNotification(
                $notification->getDatabaseMessage()
            ));
        }
    }
}
