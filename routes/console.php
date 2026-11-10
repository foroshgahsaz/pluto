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
