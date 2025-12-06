<?php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Configure middleware here
        $middleware->alias([
            'role' => App\Http\Middleware\CheckRole::class,
            'permission' => App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Appointment reminders - next day at 6 PM
        $schedule->command('appointments:send-reminders --days=1')
            ->dailyAt('18:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // Same-day reminders at 7 AM
        $schedule->command('appointments:send-reminders --days=0')
            ->dailyAt('07:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Cleanup expired locks every 15 minutes
        $schedule->command('appointments:cleanup-locks --minutes=30')
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        // Mark no-shows at midnight
        $schedule->command('appointments:mark-no-shows --hours=2')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->onOneServer();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configure exception handling here
    })
    ->create();