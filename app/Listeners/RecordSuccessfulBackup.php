<?php

namespace App\Listeners;

use App\Services\Backup\BackupRecorder;
use Spatie\Backup\Events\BackupWasSuccessful;

class RecordSuccessfulBackup
{
    public function __construct(
        protected BackupRecorder $recorder,
    ) {}

    public function handle(BackupWasSuccessful $event): void
    {
        $this->recorder->recordSuccess($event, auth()->user());
    }
}
