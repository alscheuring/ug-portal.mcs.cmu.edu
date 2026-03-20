<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Complete Your Profile')]
class ProfileCompletion extends Component
{
    use ProfileValidationRules;

    public string $name = '';

    public string $department = '';

    public string $year_in_program = '';

    public array $departmentOptions = [
        'Biological Sciences' => 'Biological Sciences',
        'Mathematical Sciences' => 'Mathematical Sciences',
        'Chemistry' => 'Chemistry',
        'Physics' => 'Physics',
    ];

    public array $yearOptions = [
        'Freshman' => 'Freshman',
        'Sophomore' => 'Sophomore',
        'Junior' => 'Junior',
        'Senior' => 'Senior',
    ];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name ?? '';
        $this->department = $user->department ?? '';
        $this->year_in_program = $user->year_in_program ?? '';
    }

    /**
     * Complete the user's profile.
     */
    public function completeProfile(): void
    {
        $user = Auth::user();

        // Validate the profile completion data
        $validated = $this->validate($this->profileCompletionRules());

        // Update the user's profile
        $user->fill($validated);
        $user->markProfileAsCompleted();
        $user->save();

        // Assign user to appropriate team based on department
        $this->assignTeamBasedOnDepartment($user);

        // Provide success feedback
        session()->flash('status', 'Profile completed successfully! Welcome to your department portal.');

        // Redirect to appropriate portal based on role
        $this->redirectBasedOnRole($user);
    }

    /**
     * Assign team based on user's department.
     */
    protected function assignTeamBasedOnDepartment($user): void
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
     * Redirect based on user role.
     */
    protected function redirectBasedOnRole($user): void
    {
        if ($user->isSuperAdmin() || $user->isTeamAdmin()) {
            $this->redirect('/admin');
        } elseif ($user->isStudent()) {
            // Redirect to team portal if available, otherwise student panel
            if ($user->team) {
                $this->redirect('/'.$user->team->slug);
            } else {
                $this->redirect('/student');
            }
        } else {
            $this->redirect('/student');
        }
    }

    public function render()
    {
        return view('livewire.settings.profile-completion');
    }
}
