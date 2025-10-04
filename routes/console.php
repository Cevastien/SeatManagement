<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessVerificationTimeouts;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule verification timeout processing every minute
Schedule::job(new ProcessVerificationTimeouts())->everyMinute()->name('process-verification-timeouts')->withoutOverlapping();
