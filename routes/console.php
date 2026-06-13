<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\IssueProgramCertificatesJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('ecap:escalate-unanswered-questions')->everyFifteenMinutes();
Schedule::job(new IssueProgramCertificatesJob())->hourly();
