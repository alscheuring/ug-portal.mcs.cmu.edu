<?php

use App\Livewire\TeamCalendar;
use App\Models\Event;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;
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

    // Create test events
    $this->publishedEvent = Event::factory()->published()->create([
        'title' => 'CS Seminar',
        'summary' => 'A computer science seminar',
        'start_datetime' => now()->addDays(5),
        'end_datetime' => now()->addDays(5)->addHours(2),
        'location' => 'Gates Hillman Center',
        'tags' => ['academic', 'seminar'],
        'team_id' => $this->team->id,
        'author_id' => $this->teamAdmin->id,
    ]);

    $this->upcomingEvent = Event::factory()->published()->upcoming()->create([
        'title' => 'Upcoming Workshop',
        'tags' => ['workshop', 'academic'],
        'team_id' => $this->team->id,
    ]);

    $this->socialEvent = Event::factory()->published()->create([
        'title' => 'Social Mixer',
        'tags' => ['social', 'networking'],
        'start_datetime' => now()->addDays(7),
        'end_datetime' => now()->addDays(7)->addHours(3),
        'team_id' => $this->team->id,
    ]);
});

describe('TeamCalendar Component Initialization', function () {
    it('renders successfully for team', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertSeeText($this->team->name.' Events')
            ->assertSeeText('CS Seminar')
            ->assertSeeText('Upcoming Workshop')
            ->assertSeeText('Social Mixer');
    });

    it('initializes with correct default values', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertSet('currentDate', now()->format('Y-m-d'))
            ->assertSet('view', 'month')
            ->assertSet('search', '')
            ->assertSet('selectedTags', [])
            ->assertSet('sourceTypeFilter', 'all')
            ->assertSet('showCreateModal', false)
            ->assertSet('showEventModal', false);
    });

    it('sets correct date range for current month', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertSet('dateRangeStart', now()->startOfMonth()->format('Y-m-d'))
            ->assertSet('dateRangeEnd', now()->endOfMonth()->format('Y-m-d'));
    });
});

describe('TeamCalendar Event Filtering', function () {
    it('filters events by search term', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('search', 'CS Seminar')
            ->assertSeeText('CS Seminar')
            ->assertDontSeeText('Social Mixer');
    });

    it('filters events by tags', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->call('addTag', 'academic')
            ->assertSet('selectedTags', ['academic'])
            ->assertSeeText('CS Seminar')
            ->assertSeeText('Upcoming Workshop')
            ->assertDontSeeText('Social Mixer');
    });

    it('filters events by multiple tags', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->call('addTag', 'academic')
            ->call('addTag', 'seminar')
            ->assertSet('selectedTags', ['academic', 'seminar'])
            ->assertSeeText('CS Seminar')
            ->assertDontSeeText('Social Mixer');
    });

    it('removes tags from filter', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->call('addTag', 'academic')
            ->call('addTag', 'social')
            ->assertSet('selectedTags', ['academic', 'social'])
            ->call('removeTag', 'academic')
            ->assertSet('selectedTags', ['social'])
            ->assertSeeText('Social Mixer')
            ->assertDontSeeText('CS Seminar');
    });

    it('filters events by source type', function () {
        $importedEvent = Event::factory()->imported()->published()->create([
            'title' => 'Imported Event',
            'team_id' => $this->team->id,
        ]);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('sourceTypeFilter', 'manual')
            ->assertSeeText('CS Seminar')
            ->assertDontSeeText('Imported Event')
            ->set('sourceTypeFilter', 'imported')
            ->assertSeeText('Imported Event')
            ->assertDontSeeText('CS Seminar');
    });

    it('clears all filters', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('search', 'test')
            ->call('addTag', 'academic')
            ->set('sourceTypeFilter', 'manual')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('selectedTags', [])
            ->assertSet('sourceTypeFilter', 'all');
    });
});

describe('TeamCalendar Navigation', function () {
    it('navigates to previous month', function () {
        $currentDate = now()->format('Y-m-d');
        $previousMonth = now()->subMonth()->format('Y-m-d');

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertSet('currentDate', $currentDate)
            ->call('previousMonth')
            ->assertSet('currentDate', $previousMonth);
    });

    it('navigates to next month', function () {
        $currentDate = now()->format('Y-m-d');
        $nextMonth = now()->addMonth()->format('Y-m-d');

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertSet('currentDate', $currentDate)
            ->call('nextMonth')
            ->assertSet('currentDate', $nextMonth);
    });

    it('goes to today', function () {
        $component = Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('currentDate', now()->addMonths(3)->format('Y-m-d'))
            ->call('goToToday')
            ->assertSet('currentDate', now()->format('Y-m-d'));
    });

    it('changes view types', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertSet('view', 'month')
            ->call('changeView', 'week')
            ->assertSet('view', 'week')
            ->call('changeView', 'day')
            ->assertSet('view', 'day')
            ->call('changeView', 'agenda')
            ->assertSet('view', 'agenda');
    });
});

describe('TeamCalendar Event Creation', function () {
    it('shows create button for team admins', function () {
        $this->actingAs($this->teamAdmin);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertSeeText('Create Event');
    });

    it('hides create button for students', function () {
        $this->actingAs($this->student);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertDontSeeText('Create Event');
    });

    it('opens create modal for authorized users', function () {
        $this->actingAs($this->teamAdmin);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->assertSet('showCreateModal', false)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->assertSeeText('Create New Event');
    });

    it('prevents opening create modal for unauthorized users', function () {
        $this->actingAs($this->student);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->call('openCreateModal')
            ->assertSet('showCreateModal', false);
    });

    it('closes create modal', function () {
        $this->actingAs($this->teamAdmin);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->call('closeCreateModal')
            ->assertSet('showCreateModal', false);
    });

    it('creates event successfully', function () {
        $this->actingAs($this->teamAdmin);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('newEvent.title', 'New Test Event')
            ->set('newEvent.summary', 'Test event summary')
            ->set('newEvent.description', 'Test event description')
            ->set('newEvent.start_datetime', now()->addDays(10)->format('Y-m-d\TH:i'))
            ->set('newEvent.end_datetime', now()->addDays(10)->addHours(2)->format('Y-m-d\TH:i'))
            ->set('newEvent.location', 'Test Location')
            ->set('newEvent.tags', ['test', 'event'])
            ->call('createEvent')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('events', [
            'title' => 'New Test Event',
            'team_id' => $this->team->id,
            'author_id' => $this->teamAdmin->id,
            'source_type' => 'manual',
        ]);
    });

    it('validates required fields for event creation', function () {
        $this->actingAs($this->teamAdmin);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('newEvent.title', '')
            ->set('newEvent.start_datetime', '')
            ->set('newEvent.end_datetime', '')
            ->call('createEvent')
            ->assertHasErrors([
                'newEvent.title' => 'required',
                'newEvent.start_datetime' => 'required',
                'newEvent.end_datetime' => 'required',
            ]);
    });

    it('validates end datetime is after start datetime', function () {
        $this->actingAs($this->teamAdmin);

        $startTime = now()->addDays(5);
        $endTime = now()->addDays(5)->subHour(); // End before start

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('newEvent.title', 'Invalid Event')
            ->set('newEvent.start_datetime', $startTime->format('Y-m-d\TH:i'))
            ->set('newEvent.end_datetime', $endTime->format('Y-m-d\TH:i'))
            ->call('createEvent')
            ->assertHasErrors(['newEvent.end_datetime']);
    });

    it('validates URL fields', function () {
        $this->actingAs($this->teamAdmin);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('newEvent.title', 'Test Event')
            ->set('newEvent.start_datetime', now()->addDays(5)->format('Y-m-d\TH:i'))
            ->set('newEvent.end_datetime', now()->addDays(5)->addHours(2)->format('Y-m-d\TH:i'))
            ->set('newEvent.info_url', 'invalid-url')
            ->set('newEvent.image_url', 'also-invalid')
            ->call('createEvent')
            ->assertHasErrors([
                'newEvent.info_url',
                'newEvent.image_url',
            ]);
    });
});

describe('TeamCalendar Event Details Modal', function () {
    it('shows event details modal', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->call('showEvent', $this->publishedEvent->id)
            ->assertSet('showEventModal', true)
            ->assertSet('selectedEvent.id', $this->publishedEvent->id)
            ->assertSeeText($this->publishedEvent->title)
            ->assertSeeText($this->publishedEvent->summary);
    });

    it('closes event details modal', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->call('showEvent', $this->publishedEvent->id)
            ->assertSet('showEventModal', true)
            ->call('closeEventModal')
            ->assertSet('showEventModal', false)
            ->assertSet('selectedEvent', null);
    });

    it('handles event click listener', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->dispatch('event-clicked', eventId: $this->publishedEvent->id)
            ->assertSet('showEventModal', true)
            ->assertSet('selectedEvent.id', $this->publishedEvent->id);
    });
});

describe('TeamCalendar Computed Properties', function () {
    it('correctly determines if user can create events', function () {
        $this->actingAs($this->teamAdmin);
        $component = Livewire::test(TeamCalendar::class, ['team' => $this->team]);
        expect($component->get('canCreateEvents'))->toBeTrue();

        $this->actingAs($this->student);
        $component = Livewire::test(TeamCalendar::class, ['team' => $this->team]);
        expect($component->get('canCreateEvents'))->toBeFalse();
    });

    it('formats current month correctly', function () {
        $component = Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('currentDate', '2024-06-15');

        expect($component->get('currentMonth'))->toBe('June 2024');
    });

    it('returns filtered events', function () {
        $component = Livewire::test(TeamCalendar::class, ['team' => $this->team]);

        $events = $component->get('events');
        expect($events)->toHaveCount(3);

        // Filter by search
        $component->set('search', 'CS Seminar');
        $filteredEvents = $component->get('events');
        expect($filteredEvents)->toHaveCount(1);
        expect($filteredEvents->first()->title)->toBe('CS Seminar');
    });

    it('returns calendar formatted events', function () {
        $component = Livewire::test(TeamCalendar::class, ['team' => $this->team]);

        $calendarEvents = $component->get('calendarEvents');
        expect($calendarEvents)->toBeArray();
        expect($calendarEvents[0])->toHaveKeys([
            'id',
            'title',
            'start',
            'end',
            'allDay',
            'backgroundColor',
            'borderColor',
            'extendedProps',
        ]);
    });
});

describe('TeamCalendar View Modes', function () {
    it('displays agenda view correctly', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('view', 'agenda')
            ->assertSeeText('CS Seminar')
            ->assertSeeText('Upcoming Workshop')
            ->assertSeeText('Social Mixer');
    });

    it('shows empty state when no events match filters', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('search', 'non-existent-event')
            ->set('view', 'agenda')
            ->assertSeeText('No events found')
            ->assertSeeText('Try adjusting your filters');
    });

    it('shows create first event message for authorized users', function () {
        $emptyTeam = Team::factory()->create(['is_active' => true]);

        $this->actingAs($this->teamAdmin);

        Livewire::test(TeamCalendar::class, ['team' => $emptyTeam])
            ->set('view', 'agenda')
            ->assertSeeText('Create First Event');
    });

    it('does not show create first event for unauthorized users', function () {
        $emptyTeam = Team::factory()->create(['is_active' => true]);

        $this->actingAs($this->student);

        Livewire::test(TeamCalendar::class, ['team' => $emptyTeam])
            ->set('view', 'agenda')
            ->assertDontSeeText('Create First Event');
    });
});

describe('TeamCalendar Event Display', function () {
    it('displays event status badges correctly', function () {
        $happeningEvent = Event::factory()->create([
            'title' => 'Happening Event',
            'start_datetime' => now()->subHour(),
            'end_datetime' => now()->addHour(),
            'team_id' => $this->team->id,
            'is_published' => true,
        ]);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('view', 'agenda')
            ->assertSeeText('Live'); // For happening events
    });

    it('displays source type badges correctly', function () {
        $importedEvent = Event::factory()->imported()->published()->create([
            'title' => 'Imported Event',
            'team_id' => $this->team->id,
        ]);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('view', 'agenda')
            ->assertSeeText('Manual') // For manual events
            ->assertSeeText('Imported'); // For imported events
    });

    it('displays event tags correctly', function () {
        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('view', 'agenda')
            ->assertSeeText('academic')
            ->assertSeeText('seminar')
            ->assertSeeText('social');
    });

    it('truncates tag display when too many tags', function () {
        $manyTagsEvent = Event::factory()->published()->create([
            'title' => 'Many Tags Event',
            'tags' => ['tag1', 'tag2', 'tag3', 'tag4', 'tag5'],
            'team_id' => $this->team->id,
        ]);

        Livewire::test(TeamCalendar::class, ['team' => $this->team])
            ->set('view', 'agenda')
            ->assertSeeText('+2'); // Should show +2 for remaining tags
    });
});
