<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ $team->name }} Events</flux:heading>
                <flux:text class="mt-1 text-zinc-600">
                    Manage and view events for your team
                </flux:text>
            </div>
            @if($canCreateEvents)
                <div class="mt-4 sm:mt-0">
                    <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                        Create Event
                    </flux:button>
                </div>
            @endif
        </div>
    </div>

    {{-- Filters Section --}}
    <flux:card class="mb-6">
        <div class="p-4 space-y-4">
            {{-- Search and Quick Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search Input --}}
                <div class="md:col-span-2">
                    <flux:input
                        wire:model.live="search"
                        placeholder="Search events..."
                        icon="magnifying-glass"
                    />
                </div>

                {{-- Source Type Filter --}}
                <div>
                    <flux:select wire:model.live="sourceTypeFilter">
                        <flux:option value="all">All Sources</flux:option>
                        <flux:option value="manual">Manual Events</flux:option>
                        <flux:option value="imported">Imported Events</flux:option>
                    </flux:select>
                </div>

                {{-- View Toggle --}}
                <div>
                    <flux:select wire:model.live="view">
                        <flux:option value="month">Month View</flux:option>
                        <flux:option value="week">Week View</flux:option>
                        <flux:option value="day">Day View</flux:option>
                        <flux:option value="agenda">Agenda View</flux:option>
                    </flux:select>
                </div>
            </div>

            {{-- Tag Filters --}}
            <div class="space-y-2">
                <flux:text class="text-sm font-medium">Filter by Tags:</flux:text>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableTags as $tag)
                        <flux:badge
                            variant="{{ in_array($tag, $selectedTags) ? 'solid' : 'soft' }}"
                            color="{{ in_array($tag, $selectedTags) ? 'blue' : 'zinc' }}"
                            class="cursor-pointer"
                            wire:click="{{ in_array($tag, $selectedTags) ? 'removeTag' : 'addTag' }}('{{ $tag }}')"
                        >
                            {{ ucfirst($tag) }}
                            @if(in_array($tag, $selectedTags))
                                <flux:icon name="x-mark" class="w-3 h-3 ml-1" />
                            @endif
                        </flux:badge>
                    @endforeach
                </div>
                @if(!empty($selectedTags) || $search || $sourceTypeFilter !== 'all')
                    <div class="mt-2">
                        <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                            Clear all filters
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
    </flux:card>

    {{-- Calendar Navigation --}}
    <flux:card class="mb-6">
        <div class="p-4">
            <div class="flex items-center justify-between">
                {{-- Navigation Controls --}}
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <flux:button variant="ghost" size="sm" wire:click="previousMonth" icon="chevron-left">
                            Previous
                        </flux:button>
                        <flux:button variant="ghost" size="sm" wire:click="nextMonth">
                            Next
                            <flux:icon name="chevron-right" class="w-4 h-4 ml-1" />
                        </flux:button>
                    </div>
                    <flux:button variant="outline" size="sm" wire:click="goToToday">
                        Today
                    </flux:button>
                </div>

                {{-- Current Month/Period --}}
                <flux:heading size="lg">{{ $currentMonth }}</flux:heading>

                {{-- Event Count --}}
                <div class="text-sm text-zinc-600">
                    {{ $events->count() }} event{{ $events->count() !== 1 ? 's' : '' }}
                </div>
            </div>
        </div>
    </flux:card>

    {{-- Calendar Display --}}
    @if($view === 'agenda')
        {{-- Agenda View --}}
        <flux:card>
            <div class="divide-y divide-zinc-200">
                @forelse($events as $event)
                    <div class="p-4 hover:bg-zinc-50 cursor-pointer" wire:click="showEvent({{ $event->id }})">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-3 h-3 rounded-full flex-shrink-0"
                                        style="background-color: {{ $this->getEventColor($event) }}"
                                    ></div>
                                    <div class="flex-1 min-w-0">
                                        <flux:heading size="sm">{{ $event->title }}</flux:heading>
                                        @if($event->summary)
                                            <flux:text class="mt-1 text-sm text-zinc-600">
                                                {{ $event->summary }}
                                            </flux:text>
                                        @endif
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                                            <span class="flex items-center">
                                                <flux:icon name="calendar" class="w-4 h-4 mr-1" />
                                                {{ $event->formatted_date_range }}
                                            </span>
                                            @if($event->location)
                                                <span class="flex items-center">
                                                    <flux:icon name="map-pin" class="w-4 h-4 mr-1" />
                                                    {{ $event->location }}
                                                </span>
                                            @endif
                                            @if($event->tags)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach(array_slice($event->tags, 0, 3) as $tag)
                                                        <flux:badge size="sm" variant="soft">{{ $tag }}</flux:badge>
                                                    @endforeach
                                                    @if(count($event->tags) > 3)
                                                        <flux:badge size="sm" variant="soft">+{{ count($event->tags) - 3 }}</flux:badge>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <flux:badge
                                    variant="soft"
                                    color="{{ $event->source_type === 'imported' ? 'zinc' : 'blue' }}"
                                    size="sm"
                                >
                                    {{ $event->source_type === 'imported' ? 'Imported' : 'Manual' }}
                                </flux:badge>
                                @if($event->isHappening())
                                    <flux:badge variant="solid" color="green" size="sm">Live</flux:badge>
                                @elseif($event->isUpcoming())
                                    <flux:badge variant="soft" color="blue" size="sm">Upcoming</flux:badge>
                                @elseif($event->isPast())
                                    <flux:badge variant="soft" color="zinc" size="sm">Past</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <flux:icon name="calendar-days" class="mx-auto h-12 w-12 text-zinc-400" />
                        <flux:heading size="lg" class="mt-4">No events found</flux:heading>
                        <flux:text class="mt-2">
                            @if(!empty($selectedTags) || $search || $sourceTypeFilter !== 'all')
                                Try adjusting your filters to see more events.
                            @else
                                There are no events scheduled for the selected time period.
                            @endif
                        </flux:text>
                        @if($canCreateEvents)
                            <div class="mt-4">
                                <flux:button variant="primary" wire:click="openCreateModal">
                                    Create First Event
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @endforelse
            </div>
        </flux:card>
    @else
        {{-- Calendar View (Month/Week/Day) --}}
        <div class="bg-white rounded-lg shadow" id="calendar-container">
            <div class="p-6">
                <div id="fullcalendar"></div>
            </div>
        </div>
    @endif

    {{-- Create Event Modal --}}
    <flux:modal name="create-event" wire:model="showCreateModal" class="max-w-2xl">
        <form wire:submit="createEvent">
            <div class="p-6">
                <flux:heading size="lg" class="mb-6">Create New Event</flux:heading>

                <div class="space-y-6">
                    {{-- Basic Information --}}
                    <div class="grid grid-cols-1 gap-4">
                        <flux:field>
                            <flux:label>Event Title *</flux:label>
                            <flux:input wire:model="newEvent.title" placeholder="Enter event title" />
                            <flux:error name="newEvent.title" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Summary</flux:label>
                            <flux:textarea wire:model="newEvent.summary" placeholder="Brief event summary (optional)" />
                            <flux:error name="newEvent.summary" />
                        </flux:field>
                    </div>

                    {{-- Date and Time --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Start Date & Time *</flux:label>
                            <flux:input type="datetime-local" wire:model="newEvent.start_datetime" />
                            <flux:error name="newEvent.start_datetime" />
                        </flux:field>

                        <flux:field>
                            <flux:label>End Date & Time *</flux:label>
                            <flux:input type="datetime-local" wire:model="newEvent.end_datetime" />
                            <flux:error name="newEvent.end_datetime" />
                        </flux:field>
                    </div>

                    {{-- Location and URLs --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Location</flux:label>
                            <flux:input wire:model="newEvent.location" placeholder="Event location (optional)" />
                            <flux:error name="newEvent.location" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Information URL</flux:label>
                            <flux:input wire:model="newEvent.info_url" placeholder="https://..." />
                            <flux:error name="newEvent.info_url" />
                        </flux:field>
                    </div>

                    {{-- Description --}}
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="newEvent.description" rows="4" placeholder="Detailed event description (optional)" />
                        <flux:error name="newEvent.description" />
                    </flux:field>

                    {{-- Tags --}}
                    <flux:field>
                        <flux:label>Tags</flux:label>
                        <div class="mt-2">
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($newEvent['tags'] as $index => $tag)
                                    <flux:badge variant="solid" color="blue" class="flex items-center">
                                        {{ $tag }}
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            class="ml-2 p-0 h-auto"
                                            wire:click="$set('newEvent.tags', {{ json_encode(array_filter($newEvent['tags'], fn($t, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH)) }})"
                                        >
                                            <flux:icon name="x-mark" class="w-3 h-3" />
                                        </flux:button>
                                    </flux:badge>
                                @endforeach
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableTags as $tag)
                                    @if(!in_array($tag, $newEvent['tags']))
                                        <flux:badge
                                            variant="soft"
                                            color="zinc"
                                            class="cursor-pointer"
                                            wire:click="$set('newEvent.tags', {{ json_encode(array_merge($newEvent['tags'], [$tag])) }})"
                                        >
                                            + {{ ucfirst($tag) }}
                                        </flux:badge>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </flux:field>

                    {{-- Publishing --}}
                    <div class="flex items-center space-x-3">
                        <flux:checkbox wire:model="newEvent.is_published" />
                        <flux:label>Publish event immediately</flux:label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 px-6 py-4 bg-zinc-50 border-t">
                <flux:button variant="ghost" wire:click="closeCreateModal">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Create Event
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Event Detail Modal --}}
    @if($selectedEvent)
        <flux:modal name="event-detail" wire:model="showEventModal" class="max-w-2xl">
            <div class="p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <flux:heading size="xl">{{ $selectedEvent->title }}</flux:heading>
                        @if($selectedEvent->summary)
                            <flux:text class="mt-2 text-lg text-zinc-600">
                                {{ $selectedEvent->summary }}
                            </flux:text>
                        @endif
                    </div>
                    <flux:button variant="ghost" wire:click="closeEventModal" icon="x-mark" />
                </div>

                <div class="space-y-6">
                    {{-- Event Status --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @if($selectedEvent->isHappening())
                            <flux:badge variant="solid" color="green">Happening Now</flux:badge>
                        @elseif($selectedEvent->isUpcoming())
                            <flux:badge variant="solid" color="blue">Upcoming</flux:badge>
                        @else
                            <flux:badge variant="soft" color="zinc">Past Event</flux:badge>
                        @endif

                        <flux:badge
                            variant="soft"
                            color="{{ $selectedEvent->source_type === 'imported' ? 'zinc' : 'blue' }}"
                        >
                            {{ $selectedEvent->source_type === 'imported' ? 'Imported Event' : 'Manual Event' }}
                        </flux:badge>
                    </div>

                    {{-- Event Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <flux:text class="font-medium text-zinc-900">Date & Time</flux:text>
                                <flux:text class="mt-1 flex items-center text-zinc-600">
                                    <flux:icon name="calendar" class="w-4 h-4 mr-2" />
                                    {{ $selectedEvent->formatted_date_range }}
                                </flux:text>
                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    Duration: {{ $selectedEvent->formatted_duration }}
                                </flux:text>
                            </div>

                            @if($selectedEvent->location)
                                <div>
                                    <flux:text class="font-medium text-zinc-900">Location</flux:text>
                                    <flux:text class="mt-1 flex items-center text-zinc-600">
                                        <flux:icon name="map-pin" class="w-4 h-4 mr-2" />
                                        {{ $selectedEvent->location }}
                                    </flux:text>
                                </div>
                            @endif

                            @if($selectedEvent->author)
                                <div>
                                    <flux:text class="font-medium text-zinc-900">Created by</flux:text>
                                    <flux:text class="mt-1 text-zinc-600">
                                        {{ $selectedEvent->author->name }}
                                    </flux:text>
                                </div>
                            @endif

                            @if($selectedEvent->eventFeed)
                                <div>
                                    <flux:text class="font-medium text-zinc-900">Imported from</flux:text>
                                    <flux:text class="mt-1 text-zinc-600">
                                        {{ $selectedEvent->eventFeed->name }}
                                    </flux:text>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-4">
                            @if($selectedEvent->tags)
                                <div>
                                    <flux:text class="font-medium text-zinc-900">Tags</flux:text>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($selectedEvent->tags as $tag)
                                            <flux:badge variant="soft">{{ $tag }}</flux:badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($selectedEvent->info_url)
                                <div>
                                    <flux:text class="font-medium text-zinc-900">More Information</flux:text>
                                    <flux:text class="mt-1">
                                        <a href="{{ $selectedEvent->info_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                                            View Details
                                            <flux:icon name="arrow-top-right-on-square" class="w-4 h-4 ml-1" />
                                        </a>
                                    </flux:text>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($selectedEvent->description)
                        <div>
                            <flux:text class="font-medium text-zinc-900">Description</flux:text>
                            <div class="mt-2 prose prose-sm max-w-none text-zinc-600">
                                {!! nl2br(e($selectedEvent->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 px-6 py-4 bg-zinc-50 border-t">
                @if($selectedEvent->info_url)
                    <flux:button variant="ghost" href="{{ $selectedEvent->info_url }}" target="_blank">
                        View Full Details
                        <flux:icon name="arrow-top-right-on-square" class="w-4 h-4 ml-1" />
                    </flux:button>
                @endif
                <flux:button variant="primary" wire:click="closeEventModal">
                    Close
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>

{{-- FullCalendar JavaScript (for non-agenda views) --}}
@script
<script>
if (document.getElementById('fullcalendar')) {
    document.addEventListener('livewire:navigated', function () {
        initFullCalendar();
    });

    function initFullCalendar() {
        const calendarEl = document.getElementById('fullcalendar');
        if (!calendarEl) return;

        // Destroy existing calendar if it exists
        if (window.calendar) {
            window.calendar.destroy();
        }

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: $wire.get('view') === 'week' ? 'timeGridWeek' :
                        $wire.get('view') === 'day' ? 'timeGridDay' : 'dayGridMonth',
            headerToolbar: false, // We handle navigation with Livewire
            height: 'auto',
            events: $wire.get('calendarEvents'),
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                $wire.dispatch('event-clicked', { eventId: info.event.id });
            },
            eventDidMount: function(info) {
                // Add hover effects and styling
                info.el.style.cursor = 'pointer';
                info.el.title = info.event.title + (info.event.extendedProps.location ?
                    ' - ' + info.event.extendedProps.location : '');
            },
            dayMaxEvents: 3, // Show max 3 events per day in month view
            moreLinkClick: 'popover',
        });

        calendar.render();
        window.calendar = calendar;

        // Listen for Livewire updates
        Livewire.on('refreshCalendar', () => {
            calendar.refetchEvents();
        });

        // Update calendar when view changes
        $wire.$watch('view', (value) => {
            const viewMap = {
                'month': 'dayGridMonth',
                'week': 'timeGridWeek',
                'day': 'timeGridDay'
            };
            if (viewMap[value]) {
                calendar.changeView(viewMap[value]);
            }
        });

        // Update calendar when events change
        $wire.$watch('calendarEvents', (events) => {
            calendar.removeAllEvents();
            calendar.addEventSource(events);
        });

        // Update calendar date when currentDate changes
        $wire.$watch('currentDate', (date) => {
            calendar.gotoDate(date);
        });
    }

    // Initialize on page load
    initFullCalendar();
}
</script>
@endscript

{{-- Add FullCalendar CSS and JS --}}
@push('head')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>
@endpush