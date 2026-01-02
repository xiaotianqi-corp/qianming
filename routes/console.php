<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CheckCertificateExpirationJob;
use App\Jobs\SyncCertificateStatusJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::job(new CheckCertificateExpirationJob)
    ->daily()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::job(new SyncCertificateStatusJob)
    ->hourly()
    ->onOneServer();

Schedule::command('queue:prune-failed --hours=168')
    ->weekly();

Schedule::command('horizon:snapshot')
    ->everyFiveMinutes();