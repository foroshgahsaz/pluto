<?php

use App\Jobs\PublishScheduledArticlesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new PublishScheduledArticlesJob)->everyMinute()->withoutOverlapping();

Schedule::command('media:purge-orphans')->weeklyOn(0, '03:30');

foreach (config('pluto.backup.database_times', ['01:00', '13:00']) as $time) {
    Schedule::command('pluto:backup database')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->name("pluto-backup-database-{$time}");
}

foreach (config('pluto.backup.files_times', ['01:30', '13:30']) as $time) {
    Schedule::command('pluto:backup files')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->name("pluto-backup-files-{$time}");
}

Schedule::command('backup:clean')->dailyAt('03:15')->withoutOverlapping();

Schedule::command('backup:monitor')->dailyAt('04:15');
