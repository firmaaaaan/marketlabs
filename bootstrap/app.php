<?php

use App\Http\Middleware\EnsureProfileIsComplete;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsLaboran;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ThrottleMutations;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'superadmin' => EnsureUserIsSuperAdmin::class,
            'laboran' => EnsureUserIsLaboran::class,
            'throttle.mutations' => ThrottleMutations::class,
            'profile.complete' => EnsureProfileIsComplete::class,
            'security.headers' => SecurityHeaders::class,
        ]);

        $middleware->append(SecurityHeaders::class);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('queue:prune-failed --hours=24')->daily();
        $schedule->command('queue:prune-batches --hours=24')->daily();
        $schedule->command('app:send-reminders')->dailyAt('08:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
