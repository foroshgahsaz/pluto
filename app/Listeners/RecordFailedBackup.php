<?php

namespace App\Listeners;

use App\Services\Backup\BackupRecorder;
use Spatie\Backup\Events\BackupHasFailed;

class RecordFailedBackup
{
    public function __construct(
        protected BackupRecorder $recorder,
    ) {}

    public function handle(BackupHasFailed $event): void
    {
        $this->recorder->recordFailure($event, auth()->user());
    }
}
