<?php

use App\Http\Controllers\Api\EventController;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public Events API
|--------------------------------------------------------------------------
|
| These routes provide public access to event data without authentication.
| They're designed for calendar integrations, feeds, and public displays.
|
*/

Route::prefix('events')->name('api.events.')->group(function () {
    // Global events endpoints
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/upcoming', [EventController::class, 'upcoming'])->name('upcoming');
    Route::get('/today', [EventController::class, 'today'])->name('today');
    Route::get('/this-week', [EventController::class, 'thisWeek'])->name('this-week');
    Route::get('/this-month', [EventController::class, 'thisMonth'])->name('this-month');
    Route::get('/calendar', [EventController::class, 'calendar'])->name('calendar');
    Route::get('/stats', [EventController::class, 'stats'])->name('stats');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');

    // Team-specific events endpoints
    Route::prefix('teams/{team:slug}')->name('team.')->group(function () {
        Route::get('/', [EventController::class, 'teamEvents'])->name('index');
        Route::get('/upcoming', function ($teamSlug, Request $request) {
            $request->merge(['upcoming' => true]);

            return app(EventController::class)->teamEvents(
                Team::where('slug', $teamSlug)->firstOrFail(),
                $request
            );
        })->name('upcoming');
        Route::get('/today', function ($teamSlug, Request $request) {
            $request->merge(['today' => true]);

            return app(EventController::class)->teamEvents(
                Team::where('slug', $teamSlug)->firstOrFail(),
                $request
            );
        })->name('today');
        Route::get('/calendar', function ($teamSlug, Request $request) {
            $team = Team::where('slug', $teamSlug)->firstOrFail();
            $request->merge(['team' => $team->slug]);

            return app(EventController::class)->calendar($request);
        })->name('calendar');
        Route::get('/stats', function ($teamSlug, Request $request) {
            $request->merge(['team' => $teamSlug]);

            return app(EventController::class)->stats($request);
        })->name('stats');
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| These routes require authentication and are intended for admin interfaces
| and authenticated applications.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Future authenticated endpoints would go here
    // For example: event creation, updating, deletion via API
});
