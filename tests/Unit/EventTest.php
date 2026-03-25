<?php

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->user = User::factory()->create(['current_team_id' => $this->team->id]);
    $this->eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);
});

describe('Event Model', function () {
    it('creates a manual event with correct attributes', function () {
        $event = Event::factory()->create([
            'title' => 'Test Event',
            'start_datetime' => '2024-06-15 10:00:00',
            'end_datetime' => '2024-06-15 12:00:00',
            'source_type' => 'manual',
            'team_id' => $this->team->id,
            'author_id' => $this->user->id,
        ]);

        expect($event->title)->toBe('Test Event');
        expect($event->source_type)->toBe('manual');
        expect($event->team_id)->toBe($this->team->id);
        expect($event->author_id)->toBe($this->user->id);
        expect($event->isManual())->toBeTrue();
        expect($event->isImported())->toBeFalse();
    });

    it('creates an imported event with correct attributes', function () {
        $event = Event::factory()->imported()->create([
            'title' => 'Imported Event',
            'source_type' => 'imported',
            'external_id' => 'ext-123',
            'team_id' => $this->team->id,
            'event_feed_id' => $this->eventFeed->id,
        ]);

        expect($event->source_type)->toBe('imported');
        expect($event->external_id)->toBe('ext-123');
        expect($event->event_feed_id)->toBe($this->eventFeed->id);
        expect($event->isImported())->toBeTrue();
        expect($event->isManual())->toBeFalse();
        expect($event->author_id)->toBeNull();
    });

    it('generates correct slug from title', function () {
        $slug = Event::generateSlug('My Test Event!');

        expect($slug)->toBe('my-test-event');
    });

    it('has proper relationships', function () {
        $event = Event::factory()->create([
            'team_id' => $this->team->id,
            'author_id' => $this->user->id,
        ]);

        expect($event->team)->toBeInstanceOf(Team::class);
        expect($event->team->id)->toBe($this->team->id);
        expect($event->author)->toBeInstanceOf(User::class);
        expect($event->author->id)->toBe($this->user->id);
    });

    it('has proper relationship with event feed for imported events', function () {
        $event = Event::factory()->imported()->create([
            'team_id' => $this->team->id,
            'event_feed_id' => $this->eventFeed->id,
        ]);

        expect($event->eventFeed)->toBeInstanceOf(EventFeed::class);
        expect($event->eventFeed->id)->toBe($this->eventFeed->id);
    });

    it('casts attributes correctly', function () {
        $event = Event::factory()->create([
            'start_datetime' => '2024-06-15 10:00:00',
            'end_datetime' => '2024-06-15 12:00:00',
            'tags' => ['academic', 'seminar'],
            'is_published' => true,
        ]);

        expect($event->start_datetime)->toBeInstanceOf(CarbonInterface::class);
        expect($event->end_datetime)->toBeInstanceOf(CarbonInterface::class);
        expect($event->tags)->toBeArray();
        expect($event->tags)->toContain('academic');
        expect($event->is_published)->toBeTrue();
    });
});

describe('Event Scopes', function () {
    beforeEach(function () {
        // Create various events for testing scopes
        $this->publishedEvent = Event::factory()->published()->create(['team_id' => $this->team->id]);
        $this->unpublishedEvent = Event::factory()->unpublished()->create(['team_id' => $this->team->id]);
        $this->upcomingEvent = Event::factory()->upcoming()->create(['team_id' => $this->team->id]);
        $this->pastEvent = Event::factory()->past()->create(['team_id' => $this->team->id]);
        $this->manualEvent = Event::factory()->manual()->create(['team_id' => $this->team->id]);
        $this->importedEvent = Event::factory()->imported()->create(['team_id' => $this->team->id]);
    });

    it('filters published events', function () {
        $published = Event::published()->get();

        expect($published)->toContain($this->publishedEvent);
        expect($published)->not->toContain($this->unpublishedEvent);
    });

    it('filters events for specific team', function () {
        $otherTeam = Team::factory()->create();
        $otherEvent = Event::factory()->create(['team_id' => $otherTeam->id]);

        $teamEvents = Event::forTeam($this->team->id)->get();

        expect($teamEvents->pluck('team_id'))->each->toBe($this->team->id);
        expect($teamEvents)->not->toContain($otherEvent);
    });

    it('filters upcoming events', function () {
        $upcoming = Event::upcoming()->get();

        expect($upcoming)->toContain($this->upcomingEvent);
        expect($upcoming)->not->toContain($this->pastEvent);
    });

    it('filters past events', function () {
        $past = Event::past()->get();

        expect($past)->toContain($this->pastEvent);
        expect($past)->not->toContain($this->upcomingEvent);
    });

    it('filters manual events', function () {
        $manual = Event::manual()->get();

        expect($manual)->toContain($this->manualEvent);
        expect($manual)->not->toContain($this->importedEvent);
    });

    it('filters imported events', function () {
        $imported = Event::imported()->get();

        expect($imported)->toContain($this->importedEvent);
        expect($imported)->not->toContain($this->manualEvent);
    });

    it('filters events by tag', function () {
        $taggedEvent = Event::factory()->withTags(['academic', 'seminar'])->create();
        $untaggedEvent = Event::factory()->withTags(['social'])->create();

        $academicEvents = Event::withTag('academic')->get();

        expect($academicEvents)->toContain($taggedEvent);
        expect($academicEvents)->not->toContain($untaggedEvent);
    });

    it('filters events by date range', function () {
        $startDate = now()->addDays(5);
        $endDate = now()->addDays(10);

        $inRangeEvent = Event::factory()->create([
            'start_datetime' => now()->addDays(7),
            'end_datetime' => now()->addDays(7)->addHours(2),
        ]);

        $outOfRangeEvent = Event::factory()->create([
            'start_datetime' => now()->addDays(15),
            'end_datetime' => now()->addDays(15)->addHours(2),
        ]);

        $rangeEvents = Event::dateRange($startDate, $endDate)->get();

        expect($rangeEvents)->toContain($inRangeEvent);
        expect($rangeEvents)->not->toContain($outOfRangeEvent);
    });
});

describe('Event Status Methods', function () {
    it('correctly identifies upcoming events', function () {
        $upcomingEvent = Event::factory()->create([
            'start_datetime' => now()->addHours(2),
            'end_datetime' => now()->addHours(4),
        ]);

        expect($upcomingEvent->isUpcoming())->toBeTrue();
        expect($upcomingEvent->isPast())->toBeFalse();
        expect($upcomingEvent->isHappening())->toBeFalse();
    });

    it('correctly identifies past events', function () {
        $pastEvent = Event::factory()->create([
            'start_datetime' => now()->subHours(4),
            'end_datetime' => now()->subHours(2),
        ]);

        expect($pastEvent->isPast())->toBeTrue();
        expect($pastEvent->isUpcoming())->toBeFalse();
        expect($pastEvent->isHappening())->toBeFalse();
    });

    it('correctly identifies currently happening events', function () {
        $happeningEvent = Event::factory()->create([
            'start_datetime' => now()->subHour(),
            'end_datetime' => now()->addHour(),
        ]);

        expect($happeningEvent->isHappening())->toBeTrue();
        expect($happeningEvent->isUpcoming())->toBeFalse();
        expect($happeningEvent->isPast())->toBeFalse();
    });
});

describe('Event Formatted Attributes', function () {
    it('formats date and time correctly', function () {
        $event = Event::factory()->create([
            'start_datetime' => '2024-06-15 14:30:00',
            'end_datetime' => '2024-06-15 16:30:00',
        ]);

        expect($event->formatted_start_date)->toBe('Jun 15, 2024');
        expect($event->formatted_start_time)->toBe('2:30 PM');
        expect($event->formatted_end_date)->toBe('Jun 15, 2024');
        expect($event->formatted_end_time)->toBe('4:30 PM');
    });

    it('formats date range for same day event', function () {
        $event = Event::factory()->create([
            'start_datetime' => '2024-06-15 14:30:00',
            'end_datetime' => '2024-06-15 16:30:00',
        ]);

        expect($event->formatted_date_range)->toBe('Jun 15, 2024 • 2:30 PM - 4:30 PM');
    });

    it('formats date range for multi-day event', function () {
        $event = Event::factory()->create([
            'start_datetime' => '2024-06-15 14:30:00',
            'end_datetime' => '2024-06-16 16:30:00',
        ]);

        expect($event->formatted_date_range)->toBe('Jun 15, 2024 - Jun 16, 2024');
    });

    it('calculates duration correctly', function () {
        $shortEvent = Event::factory()->create([
            'start_datetime' => now(),
            'end_datetime' => now()->addMinutes(30),
        ]);

        $longEvent = Event::factory()->create([
            'start_datetime' => now(),
            'end_datetime' => now()->addHours(3),
        ]);

        $multiDayEvent = Event::factory()->create([
            'start_datetime' => now(),
            'end_datetime' => now()->addDays(2),
        ]);

        expect($shortEvent->formatted_duration)->toBe('30 min');
        expect($longEvent->formatted_duration)->toBe('3.0 hours');
        expect($multiDayEvent->formatted_duration)->toBe('2 days');
    });
});

describe('Event URL Generation', function () {
    it('generates correct URL', function () {
        $event = Event::factory()->create([
            'slug' => 'test-event',
            'team_id' => $this->team->id,
        ]);

        expect($event->url)->toBe("/{$this->team->slug}/events/test-event");
    });
});
