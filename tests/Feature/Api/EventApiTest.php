<?php

use App\Models\Event;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->team = Team::factory()->create([
        'name' => 'Computer Science',
        'slug' => 'computer-science',
        'is_active' => true,
    ]);

    $this->inactiveTeam = Team::factory()->create([
        'name' => 'Inactive Team',
        'slug' => 'inactive-team',
        'is_active' => false,
    ]);

    $this->author = User::factory()->create(['current_team_id' => $this->team->id]);

    // Create various events for testing
    $this->publishedEvent = Event::factory()->published()->create([
        'title' => 'Published Event',
        'start_datetime' => now()->addDays(5),
        'end_datetime' => now()->addDays(5)->addHours(2),
        'team_id' => $this->team->id,
        'author_id' => $this->author->id,
    ]);

    $this->upcomingEvent = Event::factory()->published()->upcoming()->create([
        'title' => 'Upcoming Event',
        'team_id' => $this->team->id,
    ]);

    $this->pastEvent = Event::factory()->published()->past()->create([
        'title' => 'Past Event',
        'team_id' => $this->team->id,
    ]);

    $this->unpublishedEvent = Event::factory()->unpublished()->create([
        'title' => 'Unpublished Event',
        'team_id' => $this->team->id,
    ]);

    $this->inactiveTeamEvent = Event::factory()->published()->create([
        'title' => 'Inactive Team Event',
        'team_id' => $this->inactiveTeam->id,
    ]);
});

describe('Global Events API', function () {
    it('returns all published events from active teams', function () {
        $response = $this->getJson('/api/events');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'summary',
                        'description',
                        'start_datetime',
                        'end_datetime',
                        'location',
                        'tags',
                        'source_type',
                        'team',
                        'author',
                        'url',
                        'formatted_date_range',
                        'is_upcoming',
                        'is_past',
                        'is_happening',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('Published Event');
        expect($eventTitles)->toContain('Upcoming Event');
        expect($eventTitles)->toContain('Past Event');
        expect($eventTitles)->not->toContain('Unpublished Event');
        expect($eventTitles)->not->toContain('Inactive Team Event');
    });

    it('paginates events correctly', function () {
        // Create more events to test pagination
        Event::factory()->published()->count(25)->create(['team_id' => $this->team->id]);

        $response = $this->getJson('/api/events?per_page=10');

        $response->assertSuccessful();

        $data = $response->json();
        expect($data['data'])->toHaveCount(10);
        expect($data['meta']['per_page'])->toBe(10);
        expect($data['links']['next'])->not->toBeNull();
    });

    it('filters events by search term', function () {
        Event::factory()->published()->create([
            'title' => 'Machine Learning Workshop',
            'description' => 'Learn about AI and ML',
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson('/api/events?search=Machine Learning');

        $response->assertSuccessful();

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('Machine Learning Workshop');
    });

    it('filters events by tags', function () {
        Event::factory()->published()->withTags(['academic', 'seminar'])->create([
            'title' => 'Academic Seminar',
            'team_id' => $this->team->id,
        ]);

        Event::factory()->published()->withTags(['social'])->create([
            'title' => 'Social Event',
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson('/api/events?tags=academic');

        $response->assertSuccessful();

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('Academic Seminar');
        expect($eventTitles)->not->toContain('Social Event');
    });

    it('filters events by source type', function () {
        $manualEvent = Event::factory()->published()->manual()->create([
            'title' => 'Manual Event',
            'team_id' => $this->team->id,
        ]);

        $importedEvent = Event::factory()->published()->imported()->create([
            'title' => 'Imported Event',
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson('/api/events?source_type=manual');

        $response->assertSuccessful();

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('Manual Event');
        expect($eventTitles)->not->toContain('Imported Event');
    });

    it('filters events by date range', function () {
        $startDate = now()->addDays(10)->format('Y-m-d');
        $endDate = now()->addDays(20)->format('Y-m-d');

        $inRangeEvent = Event::factory()->published()->create([
            'title' => 'In Range Event',
            'start_datetime' => now()->addDays(15),
            'end_datetime' => now()->addDays(15)->addHours(2),
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson("/api/events?start={$startDate}&end={$endDate}");

        $response->assertSuccessful();

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('In Range Event');
    });
});

describe('Team-Specific Events API', function () {
    it('returns events for specific team', function () {
        $otherTeam = Team::factory()->create(['slug' => 'biology', 'is_active' => true]);
        $otherTeamEvent = Event::factory()->published()->create([
            'title' => 'Biology Event',
            'team_id' => $otherTeam->id,
        ]);

        $response = $this->getJson("/api/events/teams/{$this->team->slug}");

        $response->assertSuccessful();

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('Published Event');
        expect($eventTitles)->not->toContain('Biology Event');
    });

    it('returns 404 for inactive team', function () {
        $response = $this->getJson("/api/events/teams/{$this->inactiveTeam->slug}");

        $response->assertNotFound();
    });

    it('returns 404 for non-existent team', function () {
        $response = $this->getJson('/api/events/teams/non-existent-team');

        $response->assertNotFound();
    });

    it('filters team events by upcoming', function () {
        $response = $this->getJson("/api/events/teams/{$this->team->slug}/upcoming");

        $response->assertSuccessful();

        $events = collect($response->json('data'));
        expect($events->every(fn ($event) => $event['is_upcoming']))->toBeTrue();
    });
});

describe('Specialized Events API Endpoints', function () {
    it('returns only upcoming events', function () {
        $response = $this->getJson('/api/events/upcoming');

        $response->assertSuccessful();

        $events = collect($response->json('data'));
        expect($events->every(fn ($event) => $event['is_upcoming']))->toBeTrue();
        expect($events->pluck('title'))->toContain('Upcoming Event');
        expect($events->pluck('title'))->not->toContain('Past Event');
    });

    it('returns today events', function () {
        $todayEvent = Event::factory()->published()->create([
            'title' => 'Today Event',
            'start_datetime' => today()->addHours(10),
            'end_datetime' => today()->addHours(12),
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson('/api/events/today');

        $response->assertSuccessful();

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('Today Event');
    });

    it('returns this week events', function () {
        $thisWeekEvent = Event::factory()->published()->create([
            'title' => 'This Week Event',
            'start_datetime' => now()->startOfWeek()->addDays(2),
            'end_datetime' => now()->startOfWeek()->addDays(2)->addHours(2),
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson('/api/events/this-week');

        $response->assertSuccessful();

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('This Week Event');
    });

    it('returns this month events', function () {
        $thisMonthEvent = Event::factory()->published()->create([
            'title' => 'This Month Event',
            'start_datetime' => now()->startOfMonth()->addDays(10),
            'end_datetime' => now()->startOfMonth()->addDays(10)->addHours(2),
            'team_id' => $this->team->id,
        ]);

        $response = $this->getJson('/api/events/this-month');

        $response->assertSuccessful();

        $eventTitles = collect($response->json('data'))->pluck('title');
        expect($eventTitles)->toContain('This Month Event');
    });
});

describe('Individual Event API', function () {
    it('returns individual event details', function () {
        $response = $this->getJson("/api/events/{$this->publishedEvent->id}");

        $response->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => $this->publishedEvent->id,
                    'title' => $this->publishedEvent->title,
                    'slug' => $this->publishedEvent->slug,
                    'source_type' => $this->publishedEvent->source_type,
                ],
            ]);
    });

    it('returns 404 for unpublished event', function () {
        $response = $this->getJson("/api/events/{$this->unpublishedEvent->id}");

        $response->assertNotFound();
    });

    it('returns 404 for event from inactive team', function () {
        $response = $this->getJson("/api/events/{$this->inactiveTeamEvent->id}");

        $response->assertNotFound();
    });

    it('returns 404 for non-existent event', function () {
        $response = $this->getJson('/api/events/99999');

        $response->assertNotFound();
    });
});

describe('Calendar Feed API', function () {
    it('returns events in calendar format', function () {
        $response = $this->getJson('/api/events/calendar');

        $response->assertSuccessful()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'start',
                    'end',
                    'allDay',
                    'url',
                    'description',
                    'location',
                    'backgroundColor',
                    'borderColor',
                    'extendedProps',
                ],
            ]);

        $events = $response->json();
        expect(collect($events)->pluck('title'))->toContain('Published Event');
    });

    it('limits calendar events to 500 by default', function () {
        // Create many events
        Event::factory()->published()->count(600)->create(['team_id' => $this->team->id]);

        $response = $this->getJson('/api/events/calendar');

        $response->assertSuccessful();

        $events = $response->json();
        expect(count($events))->toBeLessThanOrEqual(500);
    });

    it('respects custom limit parameter', function () {
        Event::factory()->published()->count(20)->create(['team_id' => $this->team->id]);

        $response = $this->getJson('/api/events/calendar?limit=10');

        $response->assertSuccessful();

        $events = $response->json();
        expect(count($events))->toBeLessThanOrEqual(10);
    });

    it('includes proper extended props for calendar', function () {
        $response = $this->getJson('/api/events/calendar');

        $response->assertSuccessful();

        $event = collect($response->json())->first();
        expect($event['extendedProps'])->toHaveKeys([
            'team',
            'team_slug',
            'summary',
            'tags',
            'source_type',
            'author',
            'info_url',
            'image_url',
        ]);
    });
});

describe('Events Statistics API', function () {
    it('returns comprehensive statistics', function () {
        // Create additional test data
        Event::factory()->manual()->count(5)->create(['team_id' => $this->team->id]);
        Event::factory()->imported()->count(3)->create(['team_id' => $this->team->id]);

        $response = $this->getJson('/api/events/stats');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'total_events',
                'upcoming_events',
                'events_this_week',
                'events_this_month',
                'manual_events',
                'imported_events',
                'events_by_team',
                'popular_tags',
            ]);

        $stats = $response->json();
        expect($stats['total_events'])->toBeGreaterThan(0);
        expect($stats['events_by_team'])->toBeArray();
        expect($stats['popular_tags'])->toBeArray();
    });

    it('filters statistics by team', function () {
        $otherTeam = Team::factory()->create(['slug' => 'biology', 'is_active' => true]);
        Event::factory()->published()->count(5)->create(['team_id' => $otherTeam->id]);

        $response = $this->getJson("/api/events/stats?team={$this->team->slug}");

        $response->assertSuccessful();

        // Stats should be scoped to the specific team
        $stats = $response->json();
        expect($stats)->toHaveKeys([
            'total_events',
            'upcoming_events',
            'events_this_week',
            'events_this_month',
            'manual_events',
            'imported_events',
        ]);
    });
});

describe('API Response Headers and Metadata', function () {
    it('includes proper content type headers', function () {
        $response = $this->getJson('/api/events');

        $response->assertHeader('content-type', 'application/json');
    });

    it('includes metadata in responses', function () {
        $response = $this->getJson("/api/events/{$this->publishedEvent->id}");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [],
                'meta' => [
                    'version',
                    'timezone',
                    'generated_at',
                ],
            ]);
    });

    it('handles CORS for public API', function () {
        // This would be handled by CORS middleware in production
        $response = $this->getJson('/api/events');

        $response->assertSuccessful();
        // In production, you'd test for CORS headers here
    });
});
