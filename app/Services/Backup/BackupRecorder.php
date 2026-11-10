<?php

namespace App\Services\Backup;

use App\Models\BackupRecord;
use App\Models\User;
use App\Support\Backup\BackupRunContext;
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
        $type = BackupRunContext::get();

        $record = BackupRecord::query()->create([
            'status' => 'success',
            'type' => $type,
            'disk' => $event->diskName,
            'backup_name' => $event->backupName,
            'path' => $newest?->path(),
            'size_bytes' => $newest ? (int) $newest->sizeInBytes() : null,
            'message' => 'پشتیبان‌گیری '.BackupRunContext::label($type).' با موفقیت انجام شد.',
            'triggered_by' => $triggeredBy?->id,
            'finished_at' => now(),
        ]);

        activity('backup')
            ->causedBy($triggeredBy)
            ->performedOn($record)
            ->withProperties([
                'type' => $record->type,
                'disk' => $record->disk,
                'path' => $record->path,
                'size_bytes' => $record->size_bytes,
            ])
            ->log('backup.succeeded');

        $this->notifySuperAdmins(
            'پشتیبان‌گیری موفق ('.BackupRunContext::label($type).')',
            "دیسک {$record->disk}".($record->path ? " — {$record->path}" : ''),
            'success',
        );

        return $record;
    }

    public function recordFailure(BackupHasFailed $event, ?User $triggeredBy = null): BackupRecord
    {
        $type = BackupRunContext::get();

        $record = BackupRecord::query()->create([
            'status' => 'failed',
            'type' => $type,
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
                'type' => $record->type,
                'disk' => $record->disk,
                'error' => $record->message,
            ])
            ->log('backup.failed');

        $this->notifySuperAdmins(
            'پشتیبان‌گیری ناموفق ('.BackupRunContext::label($type).')',
            $record->message ?: 'خطای ناشناخته',
            'danger',
        );

        return $record;
    }

    /**
     * @return array{exit_code: int, output: string, type: string}
     */
    public function runNow(?User $actor = null, string $type = BackupRunContext::TYPE_FULL): array
    {
        if ($actor && ! $actor->isSuperAdmin()) {
            throw new Exception('فقط سوپر ادمین می‌تواند پشتیبان‌گیری را اجرا کند.');
        }

        if (! in_array($type, BackupRunContext::types(), true)) {
            throw new Exception('نوع پشتیبان‌گیری نامعتبر است.');
        }

        activity('backup')
            ->causedBy($actor)
            ->withProperties([
                'command' => 'pluto:backup',
                'type' => $type,
            ])
            ->log('backup.triggered');

        $exitCode = Artisan::call('pluto:backup', [
            'type' => $type,
            '--triggered-by' => $actor?->id,
        ]);

        return [
            'exit_code' => $exitCode,
            'output' => Artisan::output(),
            'type' => $type,
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
