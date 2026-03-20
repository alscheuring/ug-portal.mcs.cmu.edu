<?php

namespace App\Http\Controllers;

use App\Models\User;
use Lab404\Impersonate\Services\ImpersonateManager;

class ImpersonateController extends Controller
{
    /**
     * Impersonate a user.
     */
    public function take(int $id)
    {
        $user = User::findOrFail($id);

        // Check if current user can impersonate the target user
        if (! auth()->user()->canImpersonate($user) || ! $user->canBeImpersonated()) {
            abort(403, 'You cannot impersonate this user.');
        }

        $manager = app(ImpersonateManager::class);
        $manager->take(auth()->user(), $user);

        return redirect()->to('/')->with('success', 'You are now impersonating '.$user->name);
    }

    /**
     * Leave impersonation.
     */
    public function leave()
    {
        $manager = app(ImpersonateManager::class);

        if (! $manager->isImpersonating()) {
            return redirect()->to('/')->with('error', 'You are not impersonating anyone.');
        }

        $manager->leave();

        return redirect()->to('/')->with('success', 'You have stopped impersonating.');
    }
}
