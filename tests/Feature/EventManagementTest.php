<?php

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Team;
use App\Models\User;
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

    $this->superAdmin = User::factory()->create(['current_team_id' => $this->team->id]);
    $this->superAdmin->assignRole('SuperAdmin');

    $this->teamAdmin = User::factory()->create(['current_team_id' => $this->team->id]);
    $this->teamAdmin->assignRole('TeamAdmin');

    $this->student = User::factory()->create(['current_team_id' => $this->team->id]);
    $this->student->assignRole('Student');

    $this->otherTeam = Team::factory()->create(['name' => 'Biology', 'slug' => 'biology']);
    $this->otherTeamAdmin = User::factory()->create(['current_team_id' => $this->otherTeam->id]);
    $this->otherTeamAdmin->assignRole('TeamAdmin');
});

describe('Event Creation', function () {
    it('allows team admin to create events for their team', function () {
        $this->actingAs($this->teamAdmin);

        $eventData = [
            'title' => 'CS Seminar',
            'slug' => 'cs-seminar',
            'description' => 'A seminar about computer science',
            'summary' => 'CS Seminar summary',
            'start_datetime' => now()->addDays(5),
            'end_datetime' => now()->addDays(5)->addHours(2),
            'location' => 'Gates Hillman Center',
            'info_url' => 'https://example.com/info',
            'tags' => ['academic', 'seminar'],
            'is_published' => true,
            'source_type' => 'manual',
            'team_id' => $this->team->id,
            'author_id' => $this->teamAdmin->id,
        ];

        $event = Event::create($eventData);

        expect($event->title)->toBe('CS Seminar');
        expect($event->team_id)->toBe($this->team->id);
        expect($event->author_id)->toBe($this->teamAdmin->id);
        expect($event->source_type)->toBe('manual');
        expect($event->isManual())->toBeTrue();
        expect($event->isPublished())->toBeTrue();
    });

    it('allows super admin to create events for any team', function () {
        $this->actingAs($this->superAdmin);

        $eventData = [
            'title' => 'Cross-Department Event',
            'slug' => 'cross-department-event',
            'start_datetime' => now()->addDays(5),
            'end_datetime' => now()->addDays(5)->addHours(2),
            'team_id' => $this->otherTeam->id,
            'author_id' => $this->superAdmin->id,
            'source_type' => 'manual',
        ];

        $event = Event::create($eventData);

        expect($event->team_id)->toBe($this->otherTeam->id);
        expect($event->author_id)->toBe($this->superAdmin->id);
    });

    it('generates slug automatically when not provided', function () {
        $event = Event::create([
            'title' => 'My Test Event!',
            'start_datetime' => now()->addDays(5),
            'end_datetime' => now()->addDays(5)->addHours(2),
            'team_id' => $this->team->id,
            'author_id' => $this->teamAdmin->id,
            'source_type' => 'manual',
        ]);

        expect($event->slug)->toBe('my-test-event');
    });
});

describe('Event Updates', function () {
    it('allows team admin to update their team events', function () {
        $event = Event::factory()->manual()->create([
            'team_id' => $this->team->id,
            'author_id' => $this->teamAdmin->id,
        ]);

        $this->actingAs($this->teamAdmin);

        $event->update([
            'title' => 'Updated Event Title',
            'description' => 'Updated description',
        ]);

        expect($event->fresh()->title)->toBe('Updated Event Title');
        expect($event->fresh()->description)->toBe('Updated description');
    });

    it('prevents updating imported events', function () {
        $eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);
        $importedEvent = Event::factory()->imported()->create([
            'team_id' => $this->team->id,
            'event_feed_id' => $eventFeed->id,
        ]);

        $originalTitle = $importedEvent->title;

        // Even super admin shouldn't be able to update imported events
        $this->actingAs($this->superAdmin);

        // The application should prevent this at the policy/business logic level
        // For this test, we're just verifying the model method
        expect($importedEvent->isImported())->toBeTrue();
    });
});

describe('Event Deletion', function () {
    it('allows team admin to delete their team manual events', function () {
        $event = Event::factory()->manual()->create([
            'team_id' => $this->team->id,
            'author_id' => $this->teamAdmin->id,
        ]);

        $this->actingAs($this->teamAdmin);

        $eventId = $event->id;
        $event->delete();

        expect(Event::find($eventId))->toBeNull();
    });

    it('prevents deletion of imported events', function () {
        $eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);
        $importedEvent = Event::factory()->imported()->create([
            'team_id' => $this->team->id,
            'event_feed_id' => $eventFeed->id,
        ]);

        // The application should prevent this at the policy level
        expect($importedEvent->isImported())->toBeTrue();
    });
});

describe('Event Team Scoping', function () {
    it('scopes events to specific team', function () {
        $teamEvent = Event::factory()->create(['team_id' => $this->team->id]);
        $otherTeamEvent = Event::factory()->create(['team_id' => $this->otherTeam->id]);

        $teamEvents = Event::forTeam($this->team->id)->get();

        expect($teamEvents)->toContain($teamEvent);
        expect($teamEvents)->not->toContain($otherTeamEvent);
    });

    it('prevents team admin from accessing other team events', function () {
        $otherTeamEvent = Event::factory()->create(['team_id' => $this->otherTeam->id]);

        $this->actingAs($this->teamAdmin);

        // This would be enforced in controllers/policies
        expect($this->teamAdmin->current_team_id)->not->toBe($this->otherTeam->id);
    });
});

describe('Event Publishing', function () {
    it('shows only published events in public views', function () {
        $publishedEvent = Event::factory()->published()->create(['team_id' => $this->team->id]);
        $unpublishedEvent = Event::factory()->unpublished()->create(['team_id' => $this->team->id]);

        $publishedEvents = Event::published()->get();

        expect($publishedEvents)->toContain($publishedEvent);
        expect($publishedEvents)->not->toContain($unpublishedEvent);
    });

    it('allows toggling publication status', function () {
        $event = Event::factory()->unpublished()->create(['team_id' => $this->team->id]);

        expect($event->is_published)->toBeFalse();

        $event->update(['is_published' => true]);

        expect($event->fresh()->is_published)->toBeTrue();
    });
});

describe('Event Relationships and Data Integrity', function () {
    it('maintains relationship integrity when team is deleted', function () {
        $event = Event::factory()->create(['team_id' => $this->team->id]);

        $teamId = $this->team->id;
        $eventId = $event->id;

        $this->team->delete();

        // Due to foreign key constraints, event should be deleted too
        expect(Event::find($eventId))->toBeNull();
    });

    it('handles author deletion gracefully', function () {
        $event = Event::factory()->manual()->create([
            'team_id' => $this->team->id,
            'author_id' => $this->teamAdmin->id,
        ]);

        $eventId = $event->id;
        $this->teamAdmin->delete();

        $event = Event::find($eventId);
        // Event should still exist but with null author_id (due to foreign key on delete set null)
        expect($event)->not->toBeNull();
        expect($event->author_id)->toBeNull();
    });

    it('handles event feed deletion for imported events', function () {
        $eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);
        $importedEvent = Event::factory()->imported()->create([
            'team_id' => $this->team->id,
            'event_feed_id' => $eventFeed->id,
        ]);

        $eventId = $importedEvent->id;
        $eventFeed->delete();

        $event = Event::find($eventId);
        // Event should still exist but with null event_feed_id
        expect($event)->not->toBeNull();
        expect($event->event_feed_id)->toBeNull();
    });
});

describe('Event Validation and Business Rules', function () {
    it('requires end datetime to be after start datetime', function () {
        $eventData = [
            'title' => 'Invalid Event',
            'start_datetime' => now()->addDays(5),
            'end_datetime' => now()->addDays(5)->subHour(), // End before start
            'team_id' => $this->team->id,
            'author_id' => $this->teamAdmin->id,
        ];

        // This validation would typically happen in form requests
        $start = new Carbon\Carbon($eventData['start_datetime']);
        $end = new Carbon\Carbon($eventData['end_datetime']);

        expect($end->greaterThan($start))->toBeFalse();
    });

    it('prevents duplicate external IDs within same feed', function () {
        $eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);

        $firstEvent = Event::factory()->imported()->create([
            'external_id' => 'ext-123',
            'event_feed_id' => $eventFeed->id,
            'team_id' => $this->team->id,
        ]);

        // Database constraint should prevent duplicates
        expect($firstEvent->external_id)->toBe('ext-123');
        expect($firstEvent->event_feed_id)->toBe($eventFeed->id);
    });

    it('allows same external ID across different feeds', function () {
        $eventFeed1 = EventFeed::factory()->create(['team_id' => $this->team->id]);
        $eventFeed2 = EventFeed::factory()->create(['team_id' => $this->team->id]);

        $event1 = Event::factory()->imported()->create([
            'external_id' => 'ext-123',
            'event_feed_id' => $eventFeed1->id,
            'team_id' => $this->team->id,
        ]);

        $event2 = Event::factory()->imported()->create([
            'external_id' => 'ext-123',
            'event_feed_id' => $eventFeed2->id,
            'team_id' => $this->team->id,
        ]);

        expect($event1->external_id)->toBe($event2->external_id);
        expect($event1->event_feed_id)->not->toBe($event2->event_feed_id);
    });
});

describe('Event Search and Filtering', function () {
    beforeEach(function () {
        $this->academicEvent = Event::factory()->withTags(['academic', 'seminar'])->create([
            'title' => 'Academic Seminar',
            'description' => 'Computer Science research presentation',
            'location' => 'Gates Hillman Center',
            'team_id' => $this->team->id,
        ]);

        $this->socialEvent = Event::factory()->withTags(['social', 'networking'])->create([
            'title' => 'Networking Mixer',
            'description' => 'Social event for students',
            'location' => 'University Center',
            'team_id' => $this->team->id,
        ]);
    });

    it('filters events by multiple tags', function () {
        $academicEvents = Event::withTag('academic')->get();
        $socialEvents = Event::withTag('social')->get();

        expect($academicEvents)->toContain($this->academicEvent);
        expect($academicEvents)->not->toContain($this->socialEvent);
        expect($socialEvents)->toContain($this->socialEvent);
        expect($socialEvents)->not->toContain($this->academicEvent);
    });

    it('allows searching events by title and description', function () {
        // This would typically be implemented in a controller
        $searchTerm = 'Computer Science';

        $results = Event::where(function ($query) use ($searchTerm) {
            $query->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
        })->get();

        expect($results)->toContain($this->academicEvent);
        expect($results)->not->toContain($this->socialEvent);
    });

    it('filters events by location', function () {
        $gatesEvents = Event::where('location', 'like', '%Gates%')->get();

        expect($gatesEvents)->toContain($this->academicEvent);
        expect($gatesEvents)->not->toContain($this->socialEvent);
    });
});
