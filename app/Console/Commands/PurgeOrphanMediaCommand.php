<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Support\Tenancy\SiteContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeOrphanMediaCommand extends Command
{
    protected $signature = 'media:purge-orphans {--dry-run : Only list candidates}';

    protected $description = 'Remove soft-deleted media files that are no longer referenced';

    public function handle(SiteContext $siteContext): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $siteContext->withoutScope(function () use ($dryRun): void {
            Media::onlyTrashed()
                ->whereDoesntHave('articleAttachments')
                ->orderBy('id')
                ->chunkById(100, function ($items) use ($dryRun): void {
                    foreach ($items as $media) {
                        if ($dryRun) {
                            $this->line("Would purge media #{$media->id} ({$media->path})");

                            continue;
                        }

                        Storage::disk($media->disk)->delete($media->path);
                        $media->forceDelete();
                        $this->info("Purged media #{$media->id}");
                    }
                });
        });

        return self::SUCCESS;
    }
}
