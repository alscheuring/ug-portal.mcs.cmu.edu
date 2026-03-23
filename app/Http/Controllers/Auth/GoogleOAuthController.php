<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class GoogleOAuthController extends Controller
{
    /**
     * Redirect to Google OAuth provider.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['email', 'profile'])
            ->with(['hd' => 'andrew.cmu.edu']) // Restrict to CMU domain
            ->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Validate CMU email domain
            if (! str_ends_with($googleUser->getEmail(), '@andrew.cmu.edu')) {
                return redirect('/')
                    ->with('error', 'Only CMU Andrew accounts are allowed.');
            }

            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'email_verified_at' => now(),
                    'profile_photo_path' => $googleUser->getAvatar(),
                    // Don't set department yet - let user select during profile completion
                ]
            );

            $isNewUser = $user->wasRecentlyCreated;

            // Assign default Student role if no roles exist
            if (! $user->hasAnyRole()) {
                $user->assignRole('Student');
            }

            // Assign team based on department if not already assigned
            if (! $user->current_team_id && $user->department) {
                $this->assignTeamBasedOnDepartment($user);
            }

            Auth::login($user, true);

            // Redirect based on role and profile completion status
            return $this->redirectBasedOnRole($user, $isNewUser);

        } catch (\Exception $e) {
            return redirect('/')
                ->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Handle test login for development.
     */
    public function testLogin(Request $request): RedirectResponse
    {
        if (! config('app.debug') || config('app.env') === 'production') {
            abort(404);
        }

        $testEmail = env('ANDREW_TEST_USER');
        if (! $testEmail) {
            return redirect('/')->with('error', 'Test login not configured. Set ANDREW_TEST_USER in .env file.');
        }

        // Validate CMU email format
        if (! str_ends_with($testEmail, '@andrew.cmu.edu')) {
            return redirect('/')->with('error', 'ANDREW_TEST_USER must be a valid @andrew.cmu.edu email address.');
        }

        // Extract andrew_id from email for display name
        $andrewId = str_replace('@andrew.cmu.edu', '', $testEmail);

        // Find or create test user (similar to OAuth callback)
        $user = User::updateOrCreate(
            ['email' => $testEmail],
            [
                'name' => 'Test User ('.$andrewId.')',
                'email_verified_at' => now(),
                'profile_photo_path' => null,
            ]
        );

        $isNewUser = $user->wasRecentlyCreated;

        // Assign default Student role if no roles exist
        if (! $user->hasAnyRole()) {
            $user->assignRole('Student');
        }

        // Assign team based on department if not already assigned
        if (! $user->current_team_id && $user->department) {
            $this->assignTeamBasedOnDepartment($user);
        }

        Auth::login($user, true);

        // Redirect based on role and profile completion status
        return $this->redirectBasedOnRole($user, $isNewUser);
    }

    /**
     * Assign team based on user's department.
     */
    protected function assignTeamBasedOnDepartment(User $user): void
    {
        $departmentToTeamMap = [
            'Biological Sciences' => 'biosci',
            'Mathematical Sciences' => 'math',
            'Chemistry' => 'chemistry',
            'Physics' => 'physics',
        ];

        if (isset($departmentToTeamMap[$user->department])) {
            $team = Team::where('slug', $departmentToTeamMap[$user->department])->first();
            if ($team) {
                $user->update(['current_team_id' => $team->id]);
            }
        }
    }

    /**
     * Redirect user based on their role.
     */
    protected function redirectBasedOnRole(User $user, bool $isNewUser = false): RedirectResponse
    {
        // Check if profile is complete
        if (! $user->hasCompletedProfile()) {
            $message = $isNewUser
                ? 'Welcome to the CMU Portal! Please complete your profile to get started.'
                : 'Please complete your profile to continue.';

            return redirect('/student/complete-profile')
                ->with('info', $message);
        }

        // Redirect based on role
        if ($user->isSuperAdmin() || $user->isTeamAdmin()) {
            return redirect('/admin');
        }

        // Students and default redirect
        if ($user->isStudent()) {
            return redirect('/student');
        }

        // Fallback to student panel
        return redirect('/student');
    }
}
