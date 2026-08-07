<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ========== Scheduled Tasks ==========

// Cek & auto-expire subscription/trial setiap jam
Schedule::command('subscription:check')->hourly()->withoutOverlapping();

// Backup database harian jam 3 pagi
Schedule::command('backup:run --only-db')->dailyAt('03:00')->withoutOverlapping();
