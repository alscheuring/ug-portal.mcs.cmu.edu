<?php

namespace App\Policies;

use App\Models\EventFeed;
use App\Models\User;

class EventFeedPolicy
{
    /**
     * Determine whether the user can view any event feeds.
     */
    public function viewAny(User $user): bool
    {
        // TeamAdmins and SuperAdmins can view event feeds
        return $user->isTeamAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the event feed.
     */
    public function view(User $user, EventFeed $eventFeed): bool
    {
        // SuperAdmins can view any event feed
        if ($user->isSuperAdmin()) {
            return true;
        }

        // TeamAdmins can only view feeds from their own team
        return $user->isTeamAdmin() && $eventFeed->team_id === $user->current_team_id;
    }

    /**
     * Determine whether the user can create event feeds.
     */
    public function create(User $user): bool
    {
        // TeamAdmins and SuperAdmins can create event feeds
        return $user->isTeamAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the event feed.
     */
    public function update(User $user, EventFeed $eventFeed): bool
    {
        // SuperAdmins can update any event feed
        if ($user->isSuperAdmin()) {
            return true;
        }

        // TeamAdmins can only update feeds from their own team
        return $user->isTeamAdmin() && $eventFeed->team_id === $user->current_team_id;
    }

    /**
     * Determine whether the user can delete the event feed.
     */
    public function delete(User $user, EventFeed $eventFeed): bool
    {
        // SuperAdmins can delete any event feed
        if ($user->isSuperAdmin()) {
            return true;
        }

        // TeamAdmins can only delete feeds from their own team
        return $user->isTeamAdmin() && $eventFeed->team_id === $user->current_team_id;
    }

    /**
     * Determine whether the user can restore the event feed.
     */
    public function restore(User $user, EventFeed $eventFeed): bool
    {
        // Same permissions as delete
        return $this->delete($user, $eventFeed);
    }

    /**
     * Determine whether the user can permanently delete the event feed.
     */
    public function forceDelete(User $user, EventFeed $eventFeed): bool
    {
        // Only SuperAdmins can force delete event feeds
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can manually import from the event feed.
     */
    public function import(User $user, EventFeed $eventFeed): bool
    {
        // Must be able to update the feed and the feed must be active
        return $this->update($user, $eventFeed) && $eventFeed->is_active;
    }

    /**
     * Determine whether the user can test the connection for the event feed.
     */
    public function testConnection(User $user, EventFeed $eventFeed): bool
    {
        // Same permissions as update
        return $this->update($user, $eventFeed);
    }

    /**
     * Determine whether the user can manage event feeds for a specific team.
     */
    public function manageTeamFeeds(User $user, int $teamId): bool
    {
        // SuperAdmins can manage feeds for any team
        if ($user->isSuperAdmin()) {
            return true;
        }

        // TeamAdmins can only manage feeds for their own team
        return $user->isTeamAdmin() && $user->current_team_id === $teamId;
    }
}
