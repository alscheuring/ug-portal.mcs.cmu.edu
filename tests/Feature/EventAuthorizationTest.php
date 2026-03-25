<?php

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Team;
use App\Models\User;
use App\Policies\EventFeedPolicy;
use App\Policies\EventPolicy;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'SuperAdmin']);
    Role::firstOrCreate(['name' => 'TeamAdmin']);
    Role::firstOrCreate(['name' => 'Student']);

    $this->team = Team::factory()->create([
        'name' => 'Computer Science',
        'slug' => 'computer-science',
        'is_active' => true,
    ]);

    $this->otherTeam = Team::factory()->create([
        'name' => 'Biology',
        'slug' => 'biology',
        'is_active' => true,
    ]);

    $this->inactiveTeam = Team::factory()->create([
        'name' => 'Inactive Department',
        'slug' => 'inactive-department',
        'is_active' => false,
    ]);

    // Create users with different roles
    $this->superAdmin = User::factory()->create(['current_team_id' => $this->team->id]);
    $this->superAdmin->assignRole('SuperAdmin');

    $this->teamAdmin = User::factory()->create(['current_team_id' => $this->team->id]);
    $this->teamAdmin->assignRole('TeamAdmin');

    $this->otherTeamAdmin = User::factory()->create(['current_team_id' => $this->otherTeam->id]);
    $this->otherTeamAdmin->assignRole('TeamAdmin');

    $this->student = User::factory()->create(['current_team_id' => $this->team->id]);
    $this->student->assignRole('Student');

    // Create test events
    $this->manualEvent = Event::factory()->manual()->create([
        'team_id' => $this->team->id,
        'author_id' => $this->teamAdmin->id,
    ]);

    $this->importedEvent = Event::factory()->imported()->create([
        'team_id' => $this->team->id,
    ]);

    $this->otherTeamEvent = Event::factory()->manual()->create([
        'team_id' => $this->otherTeam->id,
        'author_id' => $this->otherTeamAdmin->id,
    ]);

    $this->unpublishedEvent = Event::factory()->unpublished()->create([
        'team_id' => $this->team->id,
        'author_id' => $this->teamAdmin->id,
    ]);

    $this->inactiveTeamEvent = Event::factory()->create([
        'team_id' => $this->inactiveTeam->id,
    ]);

    // Create test event feeds
    $this->eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);
    $this->otherTeamFeed = EventFeed::factory()->create(['team_id' => $this->otherTeam->id]);
});

describe('Event Policy - View Permissions', function () {
    it('allows any authenticated user to view published events from active teams', function () {
        $policy = new EventPolicy;

        expect($policy->view($this->student, $this->manualEvent))->toBeTrue();
        expect($policy->view($this->teamAdmin, $this->manualEvent))->toBeTrue();
        expect($policy->view($this->superAdmin, $this->manualEvent))->toBeTrue();
        expect($policy->view($this->otherTeamAdmin, $this->manualEvent))->toBeTrue();
    });

    it('prevents viewing unpublished events', function () {
        $policy = new EventPolicy;

        expect($policy->view($this->student, $this->unpublishedEvent))->toBeFalse();
        expect($policy->view($this->teamAdmin, $this->unpublishedEvent))->toBeFalse();
        expect($policy->view($this->superAdmin, $this->unpublishedEvent))->toBeFalse();
    });

    it('prevents viewing events from inactive teams', function () {
        $policy = new EventPolicy;

        expect($policy->view($this->student, $this->inactiveTeamEvent))->toBeFalse();
        expect($policy->view($this->teamAdmin, $this->inactiveTeamEvent))->toBeFalse();
        expect($policy->view($this->superAdmin, $this->inactiveTeamEvent))->toBeFalse();
    });

    it('allows viewing any events for authorized users', function () {
        $policy = new EventPolicy;

        expect($policy->viewAny($this->student))->toBeTrue();
        expect($policy->viewAny($this->teamAdmin))->toBeTrue();
        expect($policy->viewAny($this->superAdmin))->toBeTrue();
    });
});

describe('Event Policy - Create Permissions', function () {
    it('allows team admins and super admins to create events', function () {
        $policy = new EventPolicy;

        expect($policy->create($this->teamAdmin))->toBeTrue();
        expect($policy->create($this->superAdmin))->toBeTrue();
    });

    it('prevents students from creating events', function () {
        $policy = new EventPolicy;

        expect($policy->create($this->student))->toBeFalse();
    });
});

describe('Event Policy - Update Permissions', function () {
    it('prevents updating imported events', function () {
        $policy = new EventPolicy;

        expect($policy->update($this->superAdmin, $this->importedEvent))->toBeFalse();
        expect($policy->update($this->teamAdmin, $this->importedEvent))->toBeFalse();
    });

    it('allows super admin to update any manual event', function () {
        $policy = new EventPolicy;

        expect($policy->update($this->superAdmin, $this->manualEvent))->toBeTrue();
        expect($policy->update($this->superAdmin, $this->otherTeamEvent))->toBeTrue();
    });

    it('allows team admin to update events from their own team only', function () {
        $policy = new EventPolicy;

        expect($policy->update($this->teamAdmin, $this->manualEvent))->toBeTrue();
        expect($policy->update($this->teamAdmin, $this->otherTeamEvent))->toBeFalse();
        expect($policy->update($this->otherTeamAdmin, $this->otherTeamEvent))->toBeTrue();
        expect($policy->update($this->otherTeamAdmin, $this->manualEvent))->toBeFalse();
    });

    it('allows authors to update their own manual events', function () {
        $policy = new EventPolicy;

        expect($policy->update($this->teamAdmin, $this->manualEvent))->toBeTrue();
    });

    it('prevents students from updating events', function () {
        $policy = new EventPolicy;

        expect($policy->update($this->student, $this->manualEvent))->toBeFalse();
    });
});

describe('Event Policy - Delete Permissions', function () {
    it('prevents deleting imported events', function () {
        $policy = new EventPolicy;

        expect($policy->delete($this->superAdmin, $this->importedEvent))->toBeFalse();
        expect($policy->delete($this->teamAdmin, $this->importedEvent))->toBeFalse();
    });

    it('allows super admin to delete any manual event', function () {
        $policy = new EventPolicy;

        expect($policy->delete($this->superAdmin, $this->manualEvent))->toBeTrue();
        expect($policy->delete($this->superAdmin, $this->otherTeamEvent))->toBeTrue();
    });

    it('allows team admin to delete events from their own team only', function () {
        $policy = new EventPolicy;

        expect($policy->delete($this->teamAdmin, $this->manualEvent))->toBeTrue();
        expect($policy->delete($this->teamAdmin, $this->otherTeamEvent))->toBeFalse();
        expect($policy->delete($this->otherTeamAdmin, $this->otherTeamEvent))->toBeTrue();
        expect($policy->delete($this->otherTeamAdmin, $this->manualEvent))->toBeFalse();
    });

    it('allows authors to delete their own manual events', function () {
        $policy = new EventPolicy;

        expect($policy->delete($this->teamAdmin, $this->manualEvent))->toBeTrue();
    });

    it('prevents students from deleting events', function () {
        $policy = new EventPolicy;

        expect($policy->delete($this->student, $this->manualEvent))->toBeFalse();
    });
});

describe('Event Policy - Team Management', function () {
    it('allows super admin to manage events for any team', function () {
        $policy = new EventPolicy;

        expect($policy->manageTeamEvents($this->superAdmin, $this->team->id))->toBeTrue();
        expect($policy->manageTeamEvents($this->superAdmin, $this->otherTeam->id))->toBeTrue();
    });

    it('allows team admin to manage events for their own team only', function () {
        $policy = new EventPolicy;

        expect($policy->manageTeamEvents($this->teamAdmin, $this->team->id))->toBeTrue();
        expect($policy->manageTeamEvents($this->teamAdmin, $this->otherTeam->id))->toBeFalse();
        expect($policy->manageTeamEvents($this->otherTeamAdmin, $this->otherTeam->id))->toBeTrue();
        expect($policy->manageTeamEvents($this->otherTeamAdmin, $this->team->id))->toBeFalse();
    });

    it('prevents students from managing team events', function () {
        $policy = new EventPolicy;

        expect($policy->manageTeamEvents($this->student, $this->team->id))->toBeFalse();
    });
});

describe('EventFeed Policy - View Permissions', function () {
    it('allows team admins and super admins to view any event feeds', function () {
        $policy = new EventFeedPolicy;

        expect($policy->viewAny($this->teamAdmin))->toBeTrue();
        expect($policy->viewAny($this->superAdmin))->toBeTrue();
    });

    it('prevents students from viewing event feeds', function () {
        $policy = new EventFeedPolicy;

        expect($policy->viewAny($this->student))->toBeFalse();
    });

    it('allows super admin to view any event feed', function () {
        $policy = new EventFeedPolicy;

        expect($policy->view($this->superAdmin, $this->eventFeed))->toBeTrue();
        expect($policy->view($this->superAdmin, $this->otherTeamFeed))->toBeTrue();
    });

    it('allows team admin to view feeds from their own team only', function () {
        $policy = new EventFeedPolicy;

        expect($policy->view($this->teamAdmin, $this->eventFeed))->toBeTrue();
        expect($policy->view($this->teamAdmin, $this->otherTeamFeed))->toBeFalse();
        expect($policy->view($this->otherTeamAdmin, $this->otherTeamFeed))->toBeTrue();
        expect($policy->view($this->otherTeamAdmin, $this->eventFeed))->toBeFalse();
    });
});

describe('EventFeed Policy - Create and Update Permissions', function () {
    it('allows team admins and super admins to create event feeds', function () {
        $policy = new EventFeedPolicy;

        expect($policy->create($this->teamAdmin))->toBeTrue();
        expect($policy->create($this->superAdmin))->toBeTrue();
    });

    it('prevents students from creating event feeds', function () {
        $policy = new EventFeedPolicy;

        expect($policy->create($this->student))->toBeFalse();
    });

    it('allows super admin to update any event feed', function () {
        $policy = new EventFeedPolicy;

        expect($policy->update($this->superAdmin, $this->eventFeed))->toBeTrue();
        expect($policy->update($this->superAdmin, $this->otherTeamFeed))->toBeTrue();
    });

    it('allows team admin to update feeds from their own team only', function () {
        $policy = new EventFeedPolicy;

        expect($policy->update($this->teamAdmin, $this->eventFeed))->toBeTrue();
        expect($policy->update($this->teamAdmin, $this->otherTeamFeed))->toBeFalse();
        expect($policy->update($this->otherTeamAdmin, $this->otherTeamFeed))->toBeTrue();
        expect($policy->update($this->otherTeamAdmin, $this->eventFeed))->toBeFalse();
    });

    it('prevents students from updating event feeds', function () {
        $policy = new EventFeedPolicy;

        expect($policy->update($this->student, $this->eventFeed))->toBeFalse();
    });
});

describe('EventFeed Policy - Import Permissions', function () {
    it('allows importing from active feeds with proper permissions', function () {
        $activeFeed = EventFeed::factory()->active()->create(['team_id' => $this->team->id]);
        $policy = new EventFeedPolicy;

        expect($policy->import($this->teamAdmin, $activeFeed))->toBeTrue();
        expect($policy->import($this->superAdmin, $activeFeed))->toBeTrue();
    });

    it('prevents importing from inactive feeds', function () {
        $inactiveFeed = EventFeed::factory()->inactive()->create(['team_id' => $this->team->id]);
        $policy = new EventFeedPolicy;

        expect($policy->import($this->teamAdmin, $inactiveFeed))->toBeFalse();
        expect($policy->import($this->superAdmin, $inactiveFeed))->toBeFalse();
    });

    it('allows testing connections with proper permissions', function () {
        $policy = new EventFeedPolicy;

        expect($policy->testConnection($this->teamAdmin, $this->eventFeed))->toBeTrue();
        expect($policy->testConnection($this->superAdmin, $this->eventFeed))->toBeTrue();
        expect($policy->testConnection($this->teamAdmin, $this->otherTeamFeed))->toBeFalse();
        expect($policy->testConnection($this->student, $this->eventFeed))->toBeFalse();
    });
});

describe('EventFeed Policy - Team Management', function () {
    it('allows super admin to manage feeds for any team', function () {
        $policy = new EventFeedPolicy;

        expect($policy->manageTeamFeeds($this->superAdmin, $this->team->id))->toBeTrue();
        expect($policy->manageTeamFeeds($this->superAdmin, $this->otherTeam->id))->toBeTrue();
    });

    it('allows team admin to manage feeds for their own team only', function () {
        $policy = new EventFeedPolicy;

        expect($policy->manageTeamFeeds($this->teamAdmin, $this->team->id))->toBeTrue();
        expect($policy->manageTeamFeeds($this->teamAdmin, $this->otherTeam->id))->toBeFalse();
        expect($policy->manageTeamFeeds($this->otherTeamAdmin, $this->otherTeam->id))->toBeTrue();
        expect($policy->manageTeamFeeds($this->otherTeamAdmin, $this->team->id))->toBeFalse();
    });

    it('prevents students from managing team feeds', function () {
        $policy = new EventFeedPolicy;

        expect($policy->manageTeamFeeds($this->student, $this->team->id))->toBeFalse();
    });
});

describe('Policy Integration with Laravel Gates', function () {
    it('integrates with Laravel authorization system', function () {
        $this->actingAs($this->teamAdmin);

        // Test gates through Laravel's authorization
        expect($this->teamAdmin->can('create', Event::class))->toBeTrue();
        expect($this->teamAdmin->can('update', $this->manualEvent))->toBeTrue();
        expect($this->teamAdmin->can('update', $this->importedEvent))->toBeFalse();
        expect($this->teamAdmin->can('delete', $this->manualEvent))->toBeTrue();
        expect($this->teamAdmin->can('update', $this->otherTeamEvent))->toBeFalse();
    });

    it('integrates event feed policies with Laravel gates', function () {
        $this->actingAs($this->teamAdmin);

        expect($this->teamAdmin->can('viewAny', EventFeed::class))->toBeTrue();
        expect($this->teamAdmin->can('create', EventFeed::class))->toBeTrue();
        expect($this->teamAdmin->can('update', $this->eventFeed))->toBeTrue();
        expect($this->teamAdmin->can('update', $this->otherTeamFeed))->toBeFalse();
        expect($this->teamAdmin->can('import', $this->eventFeed))->toBeTrue();
    });

    it('prevents unauthorized actions through gates', function () {
        $this->actingAs($this->student);

        expect($this->student->can('create', Event::class))->toBeFalse();
        expect($this->student->can('update', $this->manualEvent))->toBeFalse();
        expect($this->student->can('delete', $this->manualEvent))->toBeFalse();
        expect($this->student->can('viewAny', EventFeed::class))->toBeFalse();
        expect($this->student->can('create', EventFeed::class))->toBeFalse();
    });
});

describe('Cross-Team Authorization Scenarios', function () {
    it('handles cross-team event management correctly', function () {
        // Create a super admin with a different team assignment
        $superAdminOtherTeam = User::factory()->create(['current_team_id' => $this->otherTeam->id]);
        $superAdminOtherTeam->assignRole('SuperAdmin');

        $policy = new EventPolicy;

        // Super admin should be able to manage events regardless of their team assignment
        expect($policy->update($superAdminOtherTeam, $this->manualEvent))->toBeTrue();
        expect($policy->delete($superAdminOtherTeam, $this->manualEvent))->toBeTrue();
        expect($policy->manageTeamEvents($superAdminOtherTeam, $this->team->id))->toBeTrue();
    });

    it('handles team admin role changes correctly', function () {
        $policy = new EventPolicy;

        // Initially, team admin can manage their team's events
        expect($policy->update($this->teamAdmin, $this->manualEvent))->toBeTrue();

        // If team admin changes teams, they should not be able to manage old team's events
        $this->teamAdmin->update(['current_team_id' => $this->otherTeam->id]);

        expect($policy->update($this->teamAdmin, $this->manualEvent))->toBeFalse();
        expect($policy->manageTeamEvents($this->teamAdmin, $this->team->id))->toBeFalse();
        expect($policy->manageTeamEvents($this->teamAdmin, $this->otherTeam->id))->toBeTrue();
    });

    it('handles role changes correctly', function () {
        $policy = new EventPolicy;

        // Initially, team admin can create events
        expect($policy->create($this->teamAdmin))->toBeTrue();

        // If role changes to student, should not be able to create events
        $this->teamAdmin->syncRoles(['Student']);

        expect($policy->create($this->teamAdmin))->toBeFalse();
        expect($policy->update($this->teamAdmin, $this->manualEvent))->toBeFalse();
        expect($policy->delete($this->teamAdmin, $this->manualEvent))->toBeFalse();
    });
});

describe('Special Authorization Scenarios', function () {
    it('only allows super admin to force delete events', function () {
        $policy = new EventPolicy;

        expect($policy->forceDelete($this->superAdmin, $this->manualEvent))->toBeTrue();
        expect($policy->forceDelete($this->teamAdmin, $this->manualEvent))->toBeFalse();
        expect($policy->forceDelete($this->student, $this->manualEvent))->toBeFalse();
    });

    it('allows viewing statistics for authorized users', function () {
        $policy = new EventPolicy;

        expect($policy->viewStats($this->superAdmin))->toBeTrue();
        expect($policy->viewStats($this->teamAdmin))->toBeTrue();
        expect($policy->viewStats($this->student))->toBeFalse();
    });

    it('allows exporting events for authorized users', function () {
        $policy = new EventPolicy;

        expect($policy->export($this->superAdmin))->toBeTrue();
        expect($policy->export($this->teamAdmin))->toBeTrue();
        expect($policy->export($this->student))->toBeFalse();
    });
});
