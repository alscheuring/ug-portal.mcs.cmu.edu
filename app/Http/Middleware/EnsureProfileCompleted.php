<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Allow access if user is not authenticated
        if (! $user) {
            return $next($request);
        }

        // Skip profile check for certain routes
        $excludedRoutes = [
            'student.profile',
            'student.complete-profile',
            'logout',
            'safe-logout',
            'auth.google.callback',
            'auth.google.redirect',
            'test-login',
        ];

        if (in_array($request->route()?->getName(), $excludedRoutes) ||
            str_contains($request->path(), 'livewire')) {
            return $next($request);
        }

        // Check if profile is completed
        if (! $user->hasCompletedProfile()) {
            // Redirect to profile completion page
            if (! $request->is('student/complete-profile')) {
                return redirect('/student/complete-profile');
            }
        }

        return $next($request);
    }
}
