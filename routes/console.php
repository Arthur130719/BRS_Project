<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── NetCORE Scheduled Tasks ────────────────────────────────────────
Schedule::command('netcore:auto-isolir')
    ->dailyAt('00:01')
    ->withoutOverlapping()
    ->runInBackground();
