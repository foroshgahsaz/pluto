<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class RestoreBackupCommand extends Command
{
    protected $signature = 'pluto:restore
        {archive : Absolute path to Spatie backup zip}
        {--dry-run : Validate archive contents without applying}
        {--force : Apply restore (requires maintenance mode or will enable it)}';

    protected $description = 'Validate or apply a Pluto backup archive under maintenance mode';

    public function handle(): int
    {
        $archive = (string) $this->argument('archive');
        if (! is_file($archive)) {
            $this->error("Archive not found: {$archive}");

            return self::FAILURE;
        }

        $zip = new ZipArchive;
        if ($zip->open($archive) !== true) {
            $this->error('Unable to open zip archive.');

            return self::FAILURE;
        }

        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (is_string($name)) {
                $entries[] = $name;
            }
        }
        $zip->close();

        $dbDump = collect($entries)->first(fn (string $name) => str_ends_with($name, '.sql') || str_ends_with($name, '.sqlite'));
        $hasStorage = collect($entries)->contains(fn (string $name) => str_contains($name, 'storage/app/public') || str_contains($name, 'storage/app/private') || str_contains($name, 'app/public') || str_contains($name, 'app/private'));

        $this->info('Archive inspection:');
        $this->line('- files: '.count($entries));
        $this->line('- db dump: '.($dbDump ?: 'NOT FOUND'));
        $this->line('- storage paths: '.($hasStorage ? 'yes' : 'no'));

        if (! $dbDump) {
            $this->error('No database dump found in archive.');

            return self::FAILURE;
        }

        if ($this->option('dry-run') || ! $this->option('force')) {
            $this->warn('Dry-run complete. Re-run with --force to apply on staging.');
            activity('backup')->withProperties([
                'archive' => basename($archive),
                'db_dump' => $dbDump,
                'mode' => 'dry-run',
            ])->log('backup.restore_dry_run');

            return self::SUCCESS;
        }

        if (! app()->isDownForMaintenance()) {
            $this->call('down', ['--retry' => 60]);
        }

        $temp = storage_path('app/backup-temp/restore-'.now()->format('YmdHis'));
        File::ensureDirectoryExists($temp);

        $zip = new ZipArchive;
        $zip->open($archive);
        $zip->extractTo($temp);
        $zip->close();

        $this->info("Extracted to {$temp}");
        $this->warn('Import the SQL dump manually for your DB driver, then copy storage files, migrate, cache, and run: php artisan up');
        $this->line("Detected dump path under extract: {$dbDump}");

        activity('backup')->withProperties([
            'archive' => basename($archive),
            'extract_path' => $temp,
            'db_dump' => $dbDump,
            'mode' => 'force-extract',
        ])->log('backup.restore_extracted');

        return self::SUCCESS;
    }
}
