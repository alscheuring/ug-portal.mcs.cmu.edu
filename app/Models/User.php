<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'andrew_id', 'department', 'year_in_program', 'profile_completed_at', 'current_team_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Impersonate, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profile_completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the team that the user belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Check if the user has completed their profile.
     */
    public function hasCompletedProfile(): bool
    {
        return ! is_null($this->profile_completed_at) &&
               ! is_null($this->department) &&
               ! is_null($this->year_in_program) &&
               ! is_null($this->andrew_id);
    }

    /**
     * Mark the user's profile as completed.
     */
    public function markProfileAsCompleted(): void
    {
        $this->update(['profile_completed_at' => now()]);
    }

    /**
     * Check if user is a SuperAdmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('SuperAdmin');
    }

    /**
     * Check if user is a TeamAdmin.
     */
    public function isTeamAdmin(): bool
    {
        return $this->hasRole('TeamAdmin');
    }

    /**
     * Check if user is a Student.
     */
    public function isStudent(): bool
    {
        return $this->hasRole('Student');
    }

    /**
     * Determine if the user can impersonate another user.
     */
    public function canImpersonate($user): bool
    {
        // SuperAdmins can impersonate anyone
        if ($this->isSuperAdmin()) {
            return true;
        }

        // TeamAdmins can impersonate Students only
        if ($this->isTeamAdmin() && $user->isStudent()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can be impersonated.
     */
    public function canBeImpersonated(): bool
    {
        // SuperAdmins cannot be impersonated
        if ($this->isSuperAdmin()) {
            return false;
        }

        // TeamAdmins and Students can be impersonated
        return $this->isTeamAdmin() || $this->isStudent();
    }
}
