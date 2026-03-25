<?php

use App\Http\Middleware\EnsureProfileCompleted;
use App\Jobs\ImportEventFeedJob;
use App\Models\Event;
use App\Models\EventFeed;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'profile.completed' => EnsureProfileCompleted::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Import event feeds nightly at 2:00 AM
        $schedule->call(function () {
            $activeFeeds = EventFeed::active()->get();

            foreach ($activeFeeds as $feed) {
                ImportEventFeedJob::dispatch($feed);
            }
        })->daily()->at('02:00')
            ->name('import-event-feeds')
            ->emailOutputOnFailure(['admin@example.com'])
            ->withoutOverlapping();

        // Clean up old imported events (optional - keep last 1 year)
        $schedule->call(function () {
            Event::imported()
                ->where('end_datetime', '<', now()->subYear())
                ->delete();
        })->weekly()
            ->sundays()
            ->at('03:00')
            ->name('cleanup-old-events');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
