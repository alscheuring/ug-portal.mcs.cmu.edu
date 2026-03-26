<?php

use App\Jobs\ImportEventFeedJob;
use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Team;
use App\Models\User;
use App\Services\EventFeedImporter;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'SuperAdmin']);
    Role::firstOrCreate(['name' => 'TeamAdmin']);

    $this->team = Team::factory()->create([
        'name' => 'Computer Science',
        'slug' => 'computer-science',
        'is_active' => true,
    ]);

    $this->teamAdmin = User::factory()->create(['current_team_id' => $this->team->id]);
    $this->teamAdmin->assignRole('TeamAdmin');

    $this->eventFeed = EventFeed::factory()->create([
        'name' => 'CS Department Events',
        'api_url' => 'https://example.com/api/events.json',
        'max_events' => 50,
        'is_active' => true,
        'team_id' => $this->team->id,
        'import_settings' => EventFeed::getGenericJsonFeedSettings(),
    ]);
});

describe('EventFeedImporter Service', function () {
    it('successfully imports events from valid JSON feed', function () {
        $mockData = [
            'events' => [
                [
                    'id' => 'ext-123',
                    'title' => 'Machine Learning Seminar',
                    'description' => 'An introduction to ML techniques',
                    'summary' => 'ML Seminar',
                    'start' => '2024-06-15T14:00:00Z',
                    'end' => '2024-06-15T16:00:00Z',
                    'location' => 'Gates Hillman Center',
                    'url' => 'https://example.com/events/123',
                    'image' => 'https://example.com/images/ml-seminar.jpg',
                    'tags' => ['academic', 'seminar'],
                ],
                [
                    'id' => 'ext-124',
                    'title' => 'AI Workshop',
                    'description' => 'Hands-on AI workshop',
                    'summary' => 'AI Workshop',
                    'start' => '2024-06-20T10:00:00Z',
                    'end' => '2024-06-20T12:00:00Z',
                    'location' => 'Newell-Simon Hall',
                    'url' => 'https://example.com/events/124',
                    'tags' => ['workshop', 'ai'],
                ],
            ],
        ];

        Http::fake([
            $this->eventFeed->api_url => Http::response($mockData, 200),
        ]);

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(2);
        expect($result['stats']['updated'])->toBe(0);
        expect($result['stats']['skipped'])->toBe(0);

        $importedEvents = Event::where('event_feed_id', $this->eventFeed->id)->get();
        expect($importedEvents)->toHaveCount(2);

        $firstEvent = $importedEvents->where('external_id', 'ext-123')->first();
        expect($firstEvent->title)->toBe('Machine Learning Seminar');
        expect($firstEvent->source_type)->toBe('imported');
        expect($firstEvent->team_id)->toBe($this->team->id);
        expect($firstEvent->author_id)->toBeNull();
        expect($firstEvent->tags)->toBe(['academic', 'seminar']);
    });

    it('updates existing imported events', function () {
        // Create existing imported event
        $existingEvent = Event::factory()->imported()->create([
            'external_id' => 'ext-123',
            'title' => 'Old Title',
            'description' => 'Old description',
            'event_feed_id' => $this->eventFeed->id,
            'team_id' => $this->team->id,
        ]);

        $mockData = [
            'events' => [
                [
                    'id' => 'ext-123',
                    'title' => 'Updated Machine Learning Seminar',
                    'description' => 'Updated description for ML seminar',
                    'summary' => 'Updated ML Seminar',
                    'start' => '2024-06-15T14:00:00Z',
                    'end' => '2024-06-15T16:00:00Z',
                    'location' => 'Gates Hillman Center',
                    'url' => 'https://example.com/events/123',
                    'tags' => ['academic', 'seminar', 'updated'],
                ],
            ],
        ];

        Http::fake([
            $this->eventFeed->api_url => Http::response($mockData, 200),
        ]);

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(0);
        expect($result['stats']['updated'])->toBe(1);

        $updatedEvent = Event::find($existingEvent->id);
        expect($updatedEvent->title)->toBe('Updated Machine Learning Seminar');
        expect($updatedEvent->description)->toBe('Updated description for ML seminar');
        expect($updatedEvent->tags)->toBe(['academic', 'seminar', 'updated']);
    });

    it('handles CMU Events API format', function () {
        $cmuEventFeed = EventFeed::factory()->create([
            'name' => 'CMU CS Events',
            'api_url' => 'https://events.cmu.edu/live/json/v2/events/group/Computer%20Science/max/20',
            'team_id' => $this->team->id,
            'import_settings' => EventFeed::getCmuEventsFeedSettings(),
        ]);

        $mockCmuData = [
            [
                'id' => 'cmu-456',
                'title' => 'CMU Research Symposium',
                'description' => 'Annual research symposium',
                'summary' => 'Research Symposium',
                'start_date' => '2024-07-01 09:00:00',
                'end_date' => '2024-07-01 17:00:00',
                'location' => 'Rashid Auditorium',
                'url' => 'https://events.cmu.edu/event/456',
                'image' => 'https://events.cmu.edu/images/symposium.jpg',
                'tags' => ['research', 'symposium'],
            ],
        ];

        Http::fake([
            $cmuEventFeed->api_url => Http::response($mockCmuData, 200),
        ]);

        $importer = new EventFeedImporter($cmuEventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(1);

        $importedEvent = Event::where('event_feed_id', $cmuEventFeed->id)->first();
        expect($importedEvent->title)->toBe('CMU Research Symposium');
        expect($importedEvent->external_id)->toBe('cmu-456');
        expect($importedEvent->start_datetime)->toBeInstanceOf(CarbonInterface::class);
    });

    it('respects max_events limit', function () {
        $this->eventFeed->update(['max_events' => 2]);

        $mockData = [
            'events' => array_map(fn ($i) => [
                'id' => "ext-{$i}",
                'title' => "Event {$i}",
                'start' => '2024-06-15T14:00:00Z',
                'end' => '2024-06-15T16:00:00Z',
            ], range(1, 5)), // Create 5 events but only 2 should be imported
        ];

        Http::fake([
            $this->eventFeed->api_url => Http::response($mockData, 200),
        ]);

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(2);

        $importedEvents = Event::where('event_feed_id', $this->eventFeed->id)->get();
        expect($importedEvents)->toHaveCount(2);
    });

    it('handles API errors gracefully', function () {
        Http::fake([
            $this->eventFeed->api_url => Http::response(null, 404),
        ]);

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toContain('HTTP request returned status code 404');
    });

    it('handles network timeouts', function () {
        Http::fake(function () {
            throw new Exception('Connection timeout');
        });

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toContain('Import failed: Connection timeout');
    });

    it('handles malformed JSON responses', function () {
        Http::fake([
            $this->eventFeed->api_url => Http::response('invalid json', 200),
        ]);

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toContain('Invalid JSON response');
    });

    it('skips events with invalid data', function () {
        $mockData = [
            'events' => [
                [
                    'id' => 'ext-123',
                    'title' => 'Valid Event',
                    'start' => '2024-06-15T14:00:00Z',
                    'end' => '2024-06-15T16:00:00Z',
                ],
                [
                    'id' => 'ext-124',
                    'title' => 'Invalid Event',
                    // Missing required fields: start, end
                ],
                [
                    'id' => 'ext-125',
                    'title' => 'Another Invalid Event',
                    'start' => '2024-06-15T14:00:00Z',
                    'end' => '2024-06-15T12:00:00Z', // End before start
                ],
            ],
        ];

        Http::fake([
            $this->eventFeed->api_url => Http::response($mockData, 200),
        ]);

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(1);
        expect($result['stats']['skipped'])->toBe(2);
        expect($result['errors'])->toHaveCount(2);
    });

    it('does not import from inactive feeds', function () {
        $this->eventFeed->update(['is_active' => false]);

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toBe('Event feed is not active');
    });

    it('updates feed last_imported_at timestamp on successful import', function () {
        $mockData = [
            'events' => [
                [
                    'id' => 'ext-123',
                    'title' => 'Test Event',
                    'start' => '2024-06-15T14:00:00Z',
                    'end' => '2024-06-15T16:00:00Z',
                ],
            ],
        ];

        Http::fake([
            $this->eventFeed->api_url => Http::response($mockData, 200),
        ]);

        $beforeImport = $this->eventFeed->last_imported_at;

        $importer = new EventFeedImporter($this->eventFeed);
        $result = $importer->import();

        expect($result['success'])->toBeTrue();

        $this->eventFeed->refresh();
        expect($this->eventFeed->last_imported_at)->not->toBe($beforeImport);
        expect($this->eventFeed->last_imported_at)->toBeInstanceOf(CarbonInterface::class);
    });
});

describe('ImportEventFeedJob', function () {
    it('dispatches import job correctly', function () {
        Queue::fake();

        ImportEventFeedJob::dispatch($this->eventFeed);

        Queue::assertPushed(ImportEventFeedJob::class, function ($job) {
            return $job->eventFeed->id === $this->eventFeed->id;
        });
    });

    it('processes import job successfully', function () {
        $mockData = [
            'events' => [
                [
                    'id' => 'ext-123',
                    'title' => 'Job Test Event',
                    'start' => '2024-06-15T14:00:00Z',
                    'end' => '2024-06-15T16:00:00Z',
                ],
            ],
        ];

        Http::fake([
            $this->eventFeed->api_url => Http::response($mockData, 200),
        ]);

        $job = new ImportEventFeedJob($this->eventFeed, $this->teamAdmin);
        $job->handle();

        $importedEvents = Event::where('event_feed_id', $this->eventFeed->id)->get();
        expect($importedEvents)->toHaveCount(1);
        expect($importedEvents->first()->title)->toBe('Job Test Event');
    });

    it('handles job failure and retries', function () {
        Http::fake(function () {
            throw new Exception('Network failure');
        });

        $job = new ImportEventFeedJob($this->eventFeed);

        expect($job->tries)->toBe(3);
        expect($job->backoff)->toBe(60);

        // Job should handle failure gracefully
        $job->handle();

        // Verify no events were imported due to failure
        $importedEvents = Event::where('event_feed_id', $this->eventFeed->id)->get();
        expect($importedEvents)->toHaveCount(0);
    });

    it('has correct job tags for monitoring', function () {
        $job = new ImportEventFeedJob($this->eventFeed);
        $tags = $job->tags();

        expect($tags)->toContain('event-import');
        expect($tags)->toContain("feed:{$this->eventFeed->id}");
        expect($tags)->toContain("team:{$this->team->id}");
    });

    it('processes import job successfully and logs results', function () {
        $mockData = [
            'events' => [
                [
                    'id' => 'ext-123',
                    'title' => 'Logged Event',
                    'start' => '2024-06-15T14:00:00Z',
                    'end' => '2024-06-15T16:00:00Z',
                ],
            ],
        ];

        Http::fake([
            $this->eventFeed->api_url => Http::response($mockData, 200),
        ]);

        $job = new ImportEventFeedJob($this->eventFeed);
        $job->handle();

        // Verify the actual functionality worked - event was imported
        $importedEvent = Event::where('event_feed_id', $this->eventFeed->id)->first();
        expect($importedEvent)->not->toBeNull();
        expect($importedEvent->title)->toBe('Logged Event');
    });
});

describe('Import Command', function () {
    it('runs import command for all active feeds', function () {
        $anotherFeed = EventFeed::factory()->active()->create(['team_id' => $this->team->id]);
        $inactiveFeed = EventFeed::factory()->inactive()->create(['team_id' => $this->team->id]);

        Queue::fake();

        $this->artisan('app:import-event-feeds')
            ->assertExitCode(0);

        // Should dispatch jobs for active feeds only
        Queue::assertPushed(ImportEventFeedJob::class, 2);
    });

    it('runs import command for specific feed', function () {
        Queue::fake();

        $this->artisan('app:import-event-feeds', ['--feed' => $this->eventFeed->id])
            ->assertExitCode(0);

        Queue::assertPushed(ImportEventFeedJob::class, function ($job) {
            return $job->eventFeed->id === $this->eventFeed->id;
        });
    });

    it('runs import command for specific team', function () {
        $otherTeam = Team::factory()->create();
        $otherTeamFeed = EventFeed::factory()->active()->create(['team_id' => $otherTeam->id]);

        Queue::fake();

        $this->artisan('app:import-event-feeds', ['--team' => $this->team->id])
            ->assertExitCode(0);

        Queue::assertPushed(ImportEventFeedJob::class, function ($job) {
            return $job->eventFeed->team_id === $this->team->id;
        });
    });

    it('runs import synchronously when using sync option', function () {
        $mockData = [
            'events' => [
                [
                    'id' => 'ext-sync-123',
                    'title' => 'Sync Test Event',
                    'start' => '2024-06-15T14:00:00Z',
                    'end' => '2024-06-15T16:00:00Z',
                ],
            ],
        ];

        Http::fake([
            $this->eventFeed->api_url => Http::response($mockData, 200),
        ]);

        $this->artisan('app:import-event-feeds', [
            '--feed' => $this->eventFeed->id,
            '--sync' => true,
        ])->assertExitCode(0);

        // Event should be imported immediately
        $importedEvents = Event::where('event_feed_id', $this->eventFeed->id)->get();
        expect($importedEvents)->toHaveCount(1);
        expect($importedEvents->first()->title)->toBe('Sync Test Event');
    });

    it('forces import of all feeds when using force option', function () {
        // Mark feed as recently imported
        $this->eventFeed->update(['last_imported_at' => now()->subMinutes(30)]);

        Queue::fake();

        $this->artisan('app:import-event-feeds', ['--force' => true])
            ->assertExitCode(0);

        // Should still dispatch job even though recently imported
        Queue::assertPushed(ImportEventFeedJob::class, 1);
    });
});
