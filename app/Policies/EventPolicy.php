<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view events
        return true;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        // Users can view published events from active teams
        return $event->is_published && $event->team->is_active;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        // TeamAdmins and SuperAdmins can create events
        return $user->isTeamAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        // Imported events cannot be edited
        if ($event->isImported()) {
            return false;
        }

        // SuperAdmins can update any manual event
        if ($user->isSuperAdmin()) {
            return true;
        }

        // TeamAdmins can only update events from their own team
        if ($user->isTeamAdmin()) {
            return $event->team_id === $user->current_team_id;
        }

        // Authors can update their own manual events
        return $event->author_id === $user->id && $event->isManual();
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        // Imported events cannot be deleted manually
        if ($event->isImported()) {
            return false;
        }

        // SuperAdmins can delete any manual event
        if ($user->isSuperAdmin()) {
            return true;
        }

        // TeamAdmins can delete events from their own team
        if ($user->isTeamAdmin()) {
            return $event->team_id === $user->current_team_id;
        }

        // Authors can delete their own manual events
        return $event->author_id === $user->id && $event->isManual();
    }

    /**
     * Determine whether the user can restore the event.
     */
    public function restore(User $user, Event $event): bool
    {
        // Same permissions as delete for manual events
        return $this->delete($user, $event);
    }

    /**
     * Determine whether the user can permanently delete the event.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        // Only SuperAdmins can force delete events
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can manage events for a specific team.
     */
    public function manageTeamEvents(User $user, int $teamId): bool
    {
        // SuperAdmins can manage events for any team
        if ($user->isSuperAdmin()) {
            return true;
        }

        // TeamAdmins can only manage events for their own team
        return $user->isTeamAdmin() && $user->current_team_id === $teamId;
    }

    /**
     * Determine whether the user can view event statistics.
     */
    public function viewStats(User $user): bool
    {
        // TeamAdmins and SuperAdmins can view statistics
        return $user->isTeamAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can export events.
     */
    public function export(User $user): bool
    {
        // TeamAdmins and SuperAdmins can export events
        return $user->isTeamAdmin() || $user->isSuperAdmin();
    }
}
