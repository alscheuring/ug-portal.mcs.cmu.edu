<?php

use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\PublicPortalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Public routes
Route::view('/', 'welcome')->name('home');
Route::view('/login', 'auth.login')->name('login');

// Google OAuth Authentication Routes
Route::prefix('auth/google')->name('auth.google.')->group(function () {
    Route::get('/', [GoogleOAuthController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [GoogleOAuthController::class, 'callback'])->name('callback');
});

// Test login route (development only)
Route::get('/test-login', [GoogleOAuthController::class, 'testLogin'])
    ->name('auth.test-login')
    ->middleware(['web']);

// Logout route - avoid OAuth interactions during logout
Route::post('/logout', function () {
    try {
        // Store current user for cleanup (before logout)
        $user = auth()->user();

        // Clear authentication session immediately
        Auth::guard('web')->logout();

        // Invalidate session
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Clear remember token if user was found (after logout, so use stored reference)
        if ($user) {
            $user->update(['remember_token' => null]);
        }

    } catch (Exception $e) {
        // Log error but continue with logout - don't let OAuth errors block logout
        Log::warning('Logout error (continuing anyway): '.$e->getMessage());

        // Force session cleanup even if other cleanup failed
        request()->session()->flush();
        request()->session()->regenerateToken();
    }

    return redirect('/')->with('status', 'You have been logged out successfully.');
})->name('logout');

// Safe logout route for Filament panels (avoids OAuth interactions)
Route::post('/safe-logout', function () {
    try {
        // Store current user for cleanup (before logout)
        $user = auth()->user();

        // Clear authentication session immediately without OAuth interactions
        Auth::guard('web')->logout();

        // Invalidate session completely
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Clear remember token if user was found
        if ($user) {
            $user->update(['remember_token' => null]);
        }

    } catch (Exception $e) {
        // Log error but continue - never let OAuth errors block logout
        Log::warning('Safe logout error (continuing): '.$e->getMessage());

        // Force session cleanup even if above failed
        request()->session()->flush();
        request()->session()->regenerateToken();
    }

    return redirect('/')->with('status', 'You have been logged out successfully.');
})->name('safe-logout');

// Profile completion route (accessible without completed profile)
Route::get('/student/complete-profile', function () {
    return view('livewire.profile-completion-page');
})->middleware(['auth'])->name('student.complete-profile');

// Impersonation routes
Route::middleware(['auth'])->group(function () {
    Route::get('/impersonate/take/{id}', [ImpersonateController::class, 'take'])->name('impersonate.take');
    Route::get('/impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
});

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

// Public team portal routes (must be last to avoid conflicts)
Route::prefix('{team:slug}')->name('public.')->group(function () {
    Route::get('/', [PublicPortalController::class, 'index'])->name('team.index');
    Route::get('/announcements', [PublicPortalController::class, 'announcements'])->name('team.announcements.index');
    Route::get('/announcements/{slug}', [PublicPortalController::class, 'announcement'])->name('team.announcements.show');
    Route::get('/polls', [PublicPortalController::class, 'polls'])->name('team.polls.index');
    Route::get('/polls/{poll}', [PublicPortalController::class, 'poll'])->name('team.polls.show');
    Route::get('/{slug}', [PublicPortalController::class, 'page'])->name('team.page');
});

require __DIR__.'/settings.php';
