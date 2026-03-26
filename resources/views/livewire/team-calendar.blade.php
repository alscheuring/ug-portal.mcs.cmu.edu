<div class="space-y-6">
    {{-- Calendar Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ $team->name }} Calendar</h1>

            {{-- Quick Actions --}}
            <div class="flex items-center space-x-2">
                <flux:button
                    href="{{ route('public.team.index', $team->slug) }}"
                    variant="outlined"
                    size="sm"
                    icon="arrow-left"
                >
                    Back to {{ $team->name }}
                </flux:button>

                @auth
                    @if(auth()->user()->can('view', $team))
                        <flux:button
                            href="/admin/events?tableFilters[team_id][values][0]={{ $team->id }}"
                            variant="outlined"
                            size="sm"
                            icon="cog-6-tooth"
                        >
                            Manage Events
                        </flux:button>
                    @endif
                @endauth
            </div>
        </div>

        <div class="flex items-center space-x-2">
            {{-- Search --}}
            <div class="flex items-center">
                <flux:input
                    wire:model.live="search"
                    placeholder="Search events..."
                    class="w-64"
                    icon="magnifying-glass"
                />
            </div>

            {{-- Today Button --}}
            <flux:button
                wire:click="today"
                variant="outlined"
                size="sm"
                icon="calendar-days"
            >
                Today
            </flux:button>
        </div>
    </div>

    {{-- Calendar Navigation --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <flux:button
                wire:click="previousMonth"
                variant="ghost"
                size="sm"
                icon="chevron-left"
            >
                Previous
            </flux:button>

            <h2 class="text-xl font-semibold text-gray-900 min-w-[200px] text-center">
                {{ $this->calendarData['monthName'] }}
            </h2>

            <flux:button
                wire:click="nextMonth"
                variant="ghost"
                size="sm"
                icon="chevron-right"
            >
                Next
            </flux:button>
        </div>

        {{-- View Toggle --}}
        <div class="flex items-center space-x-1 bg-gray-100 rounded-lg p-1">
            <flux:button
                variant="{{ $view === 'month' ? 'filled' : 'ghost' }}"
                size="sm"
                wire:click="\$set('view', 'month')"
            >
                Month
            </flux:button>
            <flux:button
                variant="{{ $view === 'week' ? 'filled' : 'ghost' }}"
                size="sm"
                wire:click="\$set('view', 'week')"
                disabled
            >
                Week
            </flux:button>
            <flux:button
                variant="{{ $view === 'day' ? 'filled' : 'ghost' }}"
                size="sm"
                wire:click="\$set('view', 'day')"
                disabled
            >
                Day
            </flux:button>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        {{-- Day Headers --}}
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="px-4 py-3 text-center text-sm font-medium text-gray-700 border-r border-gray-200 last:border-r-0">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Calendar Weeks --}}
        @foreach($this->calendarData['weeks'] as $week)
            <div class="grid grid-cols-7 border-b border-gray-200 last:border-b-0">
                @foreach($week as $day)
                    <div class="min-h-[120px] border-r border-gray-200 last:border-r-0 p-2 {{ !$day['isCurrentMonth'] ? 'bg-gray-50' : '' }}">
                        {{-- Date Number --}}
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium {{ $day['isToday'] ? 'bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center' : ($day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400') }}">
                                {{ $day['date']->format('j') }}
                            </span>
                        </div>

                        {{-- Events --}}
                        <div class="space-y-1">
                            @foreach($day['events']->take(3) as $event)
                                <div
                                    class="group text-xs rounded px-2 py-1 cursor-pointer transition-all duration-200 {{ $event->source_type === 'imported' ? 'bg-purple-100 text-purple-800 hover:bg-purple-200' : 'bg-blue-100 text-blue-800 hover:bg-blue-200' }}"
                                    wire:click="showEvent({{ $event->id }})"
                                    title="{{ $event->title }} - {{ $event->start_datetime->format('g:i A') }}"
                                >
                                    <div class="font-medium truncate flex items-center">
                                        @if($event->source_type === 'imported')
                                            <span class="mr-1">📥</span>
                                        @else
                                            <span class="mr-1">✏️</span>
                                        @endif
                                        {{ $event->title }}
                                    </div>
                                    <div class="{{ $event->source_type === 'imported' ? 'text-purple-600' : 'text-blue-600' }}">{{ $event->start_datetime->format('g:i A') }}</div>
                                    @if($event->location)
                                        <div class="{{ $event->source_type === 'imported' ? 'text-purple-500' : 'text-blue-500' }} truncate">📍 {{ $event->location }}</div>
                                    @endif
                                </div>
                            @endforeach

                            @if($day['events']->count() > 3)
                                <div class="text-xs text-gray-500 font-medium hover:text-gray-700 cursor-pointer"
                                     title="Click to view all events for this day">
                                    +{{ $day['events']->count() - 3 }} more
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- Loading State --}}
    <div wire:loading class="text-center py-4">
        <div class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading calendar...
        </div>
    </div>
</div>
