<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Appointment Module Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Send appointment reminders daily at 6 PM for next day appointments
Schedule::command('appointments:send-reminders --days=1')
    ->dailyAt('18:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(env('ADMIN_EMAIL'));

// Send same-day reminders at 7 AM
Schedule::command('appointments:send-reminders --days=0')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->onOneServer();

// Cleanup expired slot locks every 15 minutes
Schedule::command('appointments:cleanup-locks --minutes=30')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Mark no-shows daily at midnight
Schedule::command('appointments:mark-no-shows --hours=2')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onOneServer();

// Optional: Generate daily report
Schedule::command('appointments:daily-report')
    ->dailyAt('23:55')
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputTo(env('ADMIN_EMAIL'));