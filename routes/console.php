<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── NetCORE Scheduled Tasks ────────────────────────────────────────
$isolirTime = '00:01';
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
        $isolirTime = \App\Models\SystemSetting::get('isolir_time', '00:01');
    }
} catch (\Exception $e) {
    // Abaikan jika database belum siap
}

Schedule::command('netcore:auto-isolir')
    ->dailyAt($isolirTime)
    ->withoutOverlapping()
    ->runInBackground();
