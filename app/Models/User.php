<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'year_in_program',
        'profile_completed_at',
        'current_team_id',
        'major',
        'email_verified_at',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
     * Get the user's Andrew ID (derived from email).
     */
    public function getAndrewIdAttribute(): string
    {
        return str_replace('@andrew.cmu.edu', '', $this->email ?? '');
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
     * Get the events authored by this user.
     */
    public function authoredEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'author_id');
    }

    /**
     * Check if the user has completed their profile.
     */
    public function hasCompletedProfile(): bool
    {
        return ! is_null($this->profile_completed_at) &&
               ! is_null($this->department) &&
               ! is_null($this->year_in_program);
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
     * Determine if the user can impersonate other users.
     */
    public function canImpersonate(): bool
    {
        // SuperAdmins and TeamAdmins can impersonate users
        return $this->isSuperAdmin() || $this->isTeamAdmin();
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

        // Get the current impersonator (the user trying to impersonate)
        $impersonator = auth()->user();

        if (! $impersonator) {
            return false;
        }

        // Students can be impersonated by both SuperAdmins and TeamAdmins
        if ($this->isStudent()) {
            return $impersonator->isSuperAdmin() || $impersonator->isTeamAdmin();
        }

        // TeamAdmins can only be impersonated by SuperAdmins
        if ($this->isTeamAdmin()) {
            return $impersonator->isSuperAdmin();
        }

        return false;
    }

    /**
     * Get the appropriate redirect URL for this user based on their roles.
     */
    public function getRedirectUrl(): string
    {
        // SuperAdmins and TeamAdmins go to admin panel
        if ($this->isSuperAdmin() || $this->isTeamAdmin()) {
            return '/admin';
        }

        // Students with ONLY Student role go to their team page
        if ($this->isStudent() && $this->roles->count() === 1) {
            if ($this->team) {
                return '/'.$this->team->slug;
            }
        }

        // Fallback to student panel
        return '/student';
    }

    /**
     * Determine if the user can access the specified Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access in local environment for development
        if (app()->isLocal()) {
            return true;
        }

        // Admin panel access - only SuperAdmins and TeamAdmins
        if ($panel->getId() === 'admin') {
            return $this->isSuperAdmin() || $this->isTeamAdmin();
        }

        // Student panel access - only Students
        if ($panel->getId() === 'student') {
            return $this->isStudent();
        }

        // Deny access to any other panels by default
        return false;
    }
}
