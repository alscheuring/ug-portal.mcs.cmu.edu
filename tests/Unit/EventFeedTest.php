<?php

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->team = Team::factory()->create();
});

describe('EventFeed Model', function () {
    it('creates an event feed with correct attributes', function () {
        $eventFeed = EventFeed::factory()->create([
            'name' => 'Test Feed',
            'api_url' => 'https://example.com/events.json',
            'max_events' => 50,
            'is_active' => true,
            'team_id' => $this->team->id,
        ]);

        expect($eventFeed->name)->toBe('Test Feed');
        expect($eventFeed->api_url)->toBe('https://example.com/events.json');
        expect($eventFeed->max_events)->toBe(50);
        expect($eventFeed->is_active)->toBeTrue();
        expect($eventFeed->team_id)->toBe($this->team->id);
    });

    it('has proper relationship with team', function () {
        $eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);

        expect($eventFeed->team)->toBeInstanceOf(Team::class);
        expect($eventFeed->team->id)->toBe($this->team->id);
    });

    it('has proper relationship with events', function () {
        $eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);

        $event1 = Event::factory()->imported()->create([
            'team_id' => $this->team->id,
            'event_feed_id' => $eventFeed->id,
        ]);

        $event2 = Event::factory()->imported()->create([
            'team_id' => $this->team->id,
            'event_feed_id' => $eventFeed->id,
        ]);

        expect($eventFeed->events)->toHaveCount(2);
        expect($eventFeed->events)->toContain($event1);
        expect($eventFeed->events)->toContain($event2);
    });

    it('casts attributes correctly', function () {
        $eventFeed = EventFeed::factory()->create([
            'is_active' => true,
            'import_settings' => ['type' => 'cmu_events', 'field_mapping' => ['title' => 'title']],
            'last_imported_at' => '2024-06-15 10:00:00',
        ]);

        expect($eventFeed->is_active)->toBeTrue();
        expect($eventFeed->import_settings)->toBeArray();
        expect($eventFeed->import_settings['type'])->toBe('cmu_events');
        expect($eventFeed->last_imported_at)->toBeInstanceOf(Carbon\Carbon::class);
    });
});

describe('EventFeed Scopes', function () {
    beforeEach(function () {
        $this->activeFeed = EventFeed::factory()->active()->create(['team_id' => $this->team->id]);
        $this->inactiveFeed = EventFeed::factory()->inactive()->create(['team_id' => $this->team->id]);
        $this->recentlyImportedFeed = EventFeed::factory()->recentlyImported()->create(['team_id' => $this->team->id]);
        $this->needsImportFeed = EventFeed::factory()->needsImport()->create(['team_id' => $this->team->id]);
        $this->neverImportedFeed = EventFeed::factory()->neverImported()->create(['team_id' => $this->team->id]);
    });

    it('filters active feeds', function () {
        $activeFeeds = EventFeed::active()->get();

        expect($activeFeeds)->toContain($this->activeFeed);
        expect($activeFeeds)->not->toContain($this->inactiveFeed);
    });

    it('filters feeds for specific team', function () {
        $otherTeam = Team::factory()->create();
        $otherFeed = EventFeed::factory()->create(['team_id' => $otherTeam->id]);

        $teamFeeds = EventFeed::forTeam($this->team->id)->get();

        expect($teamFeeds->pluck('team_id'))->each->toBe($this->team->id);
        expect($teamFeeds)->not->toContain($otherFeed);
    });

    it('filters feeds needing import', function () {
        $needingImport = EventFeed::needingImport()->get();

        expect($needingImport)->toContain($this->needsImportFeed);
        expect($needingImport)->toContain($this->neverImportedFeed);
        expect($needingImport)->not->toContain($this->recentlyImportedFeed);
        expect($needingImport)->not->toContain($this->inactiveFeed);
    });
});

describe('EventFeed Status Methods', function () {
    it('correctly identifies active feeds', function () {
        $activeFeed = EventFeed::factory()->active()->create();
        $inactiveFeed = EventFeed::factory()->inactive()->create();

        expect($activeFeed->isActive())->toBeTrue();
        expect($inactiveFeed->isActive())->toBeFalse();
    });

    it('correctly identifies feeds needing import', function () {
        $neverImported = EventFeed::factory()->neverImported()->active()->create();
        $needsImport = EventFeed::factory()->needsImport()->active()->create();
        $recentlyImported = EventFeed::factory()->recentlyImported()->active()->create();
        $inactiveFeed = EventFeed::factory()->inactive()->create();

        expect($neverImported->needsImport())->toBeTrue();
        expect($needsImport->needsImport())->toBeTrue();
        expect($recentlyImported->needsImport())->toBeFalse();
        expect($inactiveFeed->needsImport())->toBeFalse();
    });

    it('returns correct import status', function () {
        $activeFeed = EventFeed::factory()->active()->recentlyImported()->create();
        $needsImportFeed = EventFeed::factory()->active()->needsImport()->create();
        $neverImportedFeed = EventFeed::factory()->active()->neverImported()->create();
        $inactiveFeed = EventFeed::factory()->inactive()->create();

        expect($activeFeed->import_status)->toBe('Up to date');
        expect($needsImportFeed->import_status)->toBe('Needs import');
        expect($neverImportedFeed->import_status)->toBe('Not imported yet');
        expect($inactiveFeed->import_status)->toBe('Inactive');
    });

    it('returns correct imported events count', function () {
        $eventFeed = EventFeed::factory()->create(['team_id' => $this->team->id]);

        Event::factory()->imported()->count(3)->create([
            'team_id' => $this->team->id,
            'event_feed_id' => $eventFeed->id,
        ]);

        expect($eventFeed->imported_events_count)->toBe(3);
    });
});

describe('EventFeed Connection Testing', function () {
    it('successfully tests connection to valid URL', function () {
        Http::fake([
            'https://example.com/events.json' => Http::response(['events' => []], 200),
        ]);

        $eventFeed = EventFeed::factory()->create([
            'api_url' => 'https://example.com/events.json',
        ]);

        $result = $eventFeed->testConnection();

        expect($result['success'])->toBeTrue();
        expect($result['message'])->toBe('Connection successful');
        expect($result['status_code'])->toBe(200);
    });

    it('handles connection failure', function () {
        Http::fake([
            'https://example.com/events.json' => Http::response(null, 404),
        ]);

        $eventFeed = EventFeed::factory()->create([
            'api_url' => 'https://example.com/events.json',
        ]);

        $result = $eventFeed->testConnection();

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toBe('HTTP Error: 404');
        expect($result['status_code'])->toBe(404);
    });

    it('handles network timeout', function () {
        Http::fake(function () {
            throw new Exception('Connection timeout');
        });

        $eventFeed = EventFeed::factory()->create([
            'api_url' => 'https://example.com/events.json',
        ]);

        $result = $eventFeed->testConnection();

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toContain('Connection failed: Connection timeout');
        expect($result['status_code'])->toBeNull();
    });
});

describe('EventFeed Default Settings', function () {
    it('returns correct CMU events feed settings', function () {
        $settings = EventFeed::getCmuEventsFeedSettings();

        expect($settings['type'])->toBe('cmu_events');
        expect($settings['field_mapping'])->toBeArray();
        expect($settings['field_mapping']['title'])->toBe('title');
        expect($settings['field_mapping']['start_datetime'])->toBe('start_date');
        expect($settings['date_format'])->toBe('Y-m-d H:i:s');
        expect($settings['timezone'])->toBe('America/New_York');
    });

    it('returns correct generic JSON feed settings', function () {
        $settings = EventFeed::getGenericJsonFeedSettings();

        expect($settings['type'])->toBe('generic_json');
        expect($settings['field_mapping'])->toBeArray();
        expect($settings['field_mapping']['title'])->toBe('title');
        expect($settings['field_mapping']['start_datetime'])->toBe('start');
        expect($settings['date_format'])->toBe('c');
        expect($settings['timezone'])->toBe('UTC');
    });
});

describe('EventFeed Management', function () {
    it('marks feed as imported', function () {
        $eventFeed = EventFeed::factory()->neverImported()->create();

        expect($eventFeed->last_imported_at)->toBeNull();

        $eventFeed->markAsImported();
        $eventFeed->refresh();

        expect($eventFeed->last_imported_at)->not->toBeNull();
        expect($eventFeed->last_imported_at)->toBeInstanceOf(Carbon\Carbon::class);
    });

    it('can be activated and deactivated', function () {
        $eventFeed = EventFeed::factory()->inactive()->create();

        expect($eventFeed->is_active)->toBeFalse();

        $eventFeed->activate();
        $eventFeed->refresh();

        expect($eventFeed->is_active)->toBeTrue();

        $eventFeed->deactivate();
        $eventFeed->refresh();

        expect($eventFeed->is_active)->toBeFalse();
    });

    it('formats last imported diff correctly', function () {
        $recentFeed = EventFeed::factory()->create([
            'last_imported_at' => now()->subHours(2),
        ]);

        $neverImported = EventFeed::factory()->neverImported()->create();

        expect($recentFeed->last_imported_diff)->toContain('hours ago');
        expect($neverImported->last_imported_diff)->toBe('Never imported');
    });
});
