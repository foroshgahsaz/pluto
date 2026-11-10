<?php

namespace App\Console\Commands;

use App\Support\Backup\BackupRunContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class RunTypedBackupCommand extends Command
{
    protected $signature = 'pluto:backup
        {type : database|files|full}
        {--triggered-by= : Optional user id for audit}';

    protected $description = 'Run a typed Spatie backup (database, files, or full)';

    public function handle(): int
    {
        $type = (string) $this->argument('type');

        if (! in_array($type, BackupRunContext::types(), true)) {
            $this->error('نوع پشتیبان نامعتبر است. مقادیر مجاز: database, files, full');

            return self::FAILURE;
        }

        BackupRunContext::set($type);

        $prefix = match ($type) {
            BackupRunContext::TYPE_DATABASE => 'db-',
            BackupRunContext::TYPE_FILES => 'files-',
            default => 'full-',
        };

        config([
            'backup.backup.destination.filename_prefix' => $prefix,
        ]);

        $options = match ($type) {
            BackupRunContext::TYPE_DATABASE => ['--only-db' => true],
            BackupRunContext::TYPE_FILES => ['--only-files' => true],
            default => [],
        };

        try {
            $exit = Artisan::call('backup:run', $options);
            $this->output->write(Artisan::output());

            return $exit === 0 ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            BackupRunContext::clear();
        }
    }
}
