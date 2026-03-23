<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected $teamAdminAssignments = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle team admin assignments separately
        $teamAdminAssignments = $data['team_admins'] ?? [];
        unset($data['team_admins']);

        // Store for processing after create
        $this->teamAdminAssignments = $teamAdminAssignments;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Only process team admin assignments if user is SuperAdmin
        if (! auth()->user()->isSuperAdmin()) {
            return;
        }

        // Get TeamAdmin role
        $teamAdminRole = Role::where('name', 'TeamAdmin')->first();
        if (! $teamAdminRole) {
            return;
        }

        // Get new assignments
        $newUserIds = collect($this->teamAdminAssignments)
            ->pluck('user_id')
            ->filter()
            ->toArray();

        // Add TeamAdmin role and team assignment to new users
        foreach ($newUserIds as $userId) {
            $user = User::find($userId);
            if ($user && ! $user->hasRole('SuperAdmin')) {
                // Assign TeamAdmin role if they don't have it
                if (! $user->hasRole('TeamAdmin')) {
                    $user->assignRole('TeamAdmin');
                }
                // Set their current team to this team
                $user->update(['current_team_id' => $this->record->id]);
            }
        }
    }
}
