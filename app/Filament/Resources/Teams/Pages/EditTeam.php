<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditTeam extends EditRecord
{
    protected static string $resource = TeamResource::class;

    protected $teamAdminAssignments = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load current team admins for the repeater
        $teamAdmins = User::whereHas('roles', function ($query) {
            $query->where('name', 'TeamAdmin');
        })
            ->where('current_team_id', $this->record->id)
            ->get()
            ->map(function ($user) {
                return ['user_id' => $user->id];
            })
            ->toArray();

        $data['team_admins'] = $teamAdmins;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle team admin assignments separately
        $teamAdminAssignments = $data['team_admins'] ?? [];
        unset($data['team_admins']);

        // Store for processing after save
        $this->teamAdminAssignments = $teamAdminAssignments;

        return $data;
    }

    protected function afterSave(): void
    {
        $user = auth()->user();

        // Only process team admin assignments if user is SuperAdmin or TeamAdmin
        if (! $user->isSuperAdmin() && ! $user->isTeamAdmin()) {
            return;
        }

        // TeamAdmins can only manage admins for their own team
        if ($user->isTeamAdmin() && ! $user->isSuperAdmin()) {
            if ($user->current_team_id !== $this->record->id) {
                return;
            }
        }

        // Get TeamAdmin role
        $teamAdminRole = Role::where('name', 'TeamAdmin')->first();
        if (! $teamAdminRole) {
            return;
        }

        // Get current team admins for this team
        $currentTeamAdmins = User::whereHas('roles', function ($query) {
            $query->where('name', 'TeamAdmin');
        })
            ->where('current_team_id', $this->record->id)
            ->get();

        // Get new assignments
        $newUserIds = collect($this->teamAdminAssignments)
            ->pluck('user_id')
            ->filter()
            ->toArray();

        // Remove TeamAdmin role and team assignment from users no longer in the list
        $currentTeamAdmins->each(function ($user) use ($newUserIds) {
            if (! in_array($user->id, $newUserIds)) {
                // Remove TeamAdmin role
                $user->removeRole('TeamAdmin');
                // Set current_team_id to null if they have no other roles
                if ($user->roles->count() === 0) {
                    $user->update(['current_team_id' => null]);
                }
            }
        });

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
