<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RestoreGuideCommand extends Command
{
    protected $signature = 'pluto:restore-guide';

    protected $description = 'Print the controlled Pluto backup restore procedure';

    public function handle(): int
    {
        $this->info('Pluto restore procedure (run on staging first):');
        $this->line('1) php artisan down');
        $this->line('2) Restore DB dump from the backup archive (mysql/sqlite import)');
        $this->line('3) Extract storage files (app/public + app/private) from the archive');
        $this->line('4) php artisan migrate --force');
        $this->line('5) php artisan config:cache && php artisan route:cache && php artisan view:cache');
        $this->line('6) php artisan queue:restart');
        $this->line('7) Smoke-test /admin login + one site isolation check');
        $this->line('8) php artisan up');
        $this->line('9) Record activity: backup.restored (manual/admin note)');
        $this->newLine();
        $this->warn('Restore is intentionally CLI/maintenance-mode only — not exposed as a one-click Filament action.');

        return self::SUCCESS;
    }
}
