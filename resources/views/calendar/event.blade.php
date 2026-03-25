@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb --}}
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumb href="{{ route('team.show', $team->slug) }}">{{ $team->name }}</flux:breadcrumb>
        <flux:breadcrumb href="{{ route('calendar.team', $team->slug) }}">Events</flux:breadcrumb>
        <flux:breadcrumb>{{ $event->title }}</flux:breadcrumb>
    </flux:breadcrumbs>

    {{-- Event Header --}}
    <div class="mb-8">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <flux:heading size="2xl" level="1" class="mb-2">{{ $event->title }}</flux:heading>
                @if($event->summary)
                    <flux:text class="text-lg text-zinc-600 mb-4">{{ $event->summary }}</flux:text>
                @endif

                {{-- Status Badges --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @if($event->isHappening())
                        <flux:badge variant="solid" color="green" size="lg">
                            <flux:icon name="signal" class="w-4 h-4 mr-1" />
                            Happening Now
                        </flux:badge>
                    @elseif($event->isUpcoming())
                        <flux:badge variant="solid" color="blue" size="lg">
                            <flux:icon name="clock" class="w-4 h-4 mr-1" />
                            Upcoming
                        </flux:badge>
                    @else
                        <flux:badge variant="soft" color="zinc" size="lg">
                            <flux:icon name="archive-box" class="w-4 h-4 mr-1" />
                            Past Event
                        </flux:badge>
                    @endif

                    <flux:badge
                        variant="soft"
                        color="{{ $event->source_type === 'imported' ? 'zinc' : 'blue' }}"
                        size="lg"
                    >
                        <flux:icon name="{{ $event->source_type === 'imported' ? 'arrow-down-tray' : 'pencil' }}" class="w-4 h-4 mr-1" />
                        {{ $event->source_type === 'imported' ? 'Imported Event' : 'Manual Event' }}
                    </flux:badge>
                </div>
            </div>

            {{-- Actions --}}
            <div class="ml-4 flex items-center space-x-2">
                <flux:button variant="ghost" href="{{ route('calendar.team', $team->slug) }}">
                    <flux:icon name="arrow-left" class="w-4 h-4 mr-1" />
                    Back to Calendar
                </flux:button>
                @if($event->info_url)
                    <flux:button variant="primary" href="{{ $event->info_url }}" target="_blank">
                        View Details
                        <flux:icon name="arrow-top-right-on-square" class="w-4 h-4 ml-1" />
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Event Description --}}
            @if($event->description)
                <flux:card>
                    <div class="p-6">
                        <flux:heading size="lg" class="mb-4">About This Event</flux:heading>
                        <div class="prose prose-zinc max-w-none">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>
                </flux:card>
            @endif

            {{-- Event Image --}}
            @if($event->image_url)
                <flux:card>
                    <div class="p-6">
                        <flux:heading size="lg" class="mb-4">Event Image</flux:heading>
                        <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full rounded-lg">
                    </div>
                </flux:card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Event Details --}}
            <flux:card>
                <div class="p-6">
                    <flux:heading size="lg" class="mb-4">Event Details</flux:heading>
                    <div class="space-y-4">
                        {{-- Date and Time --}}
                        <div>
                            <flux:text class="font-medium text-zinc-900 flex items-center mb-2">
                                <flux:icon name="calendar" class="w-5 h-5 mr-2" />
                                Date & Time
                            </flux:text>
                            <flux:text class="text-zinc-600">
                                {{ $event->formatted_date_range }}
                            </flux:text>
                            <flux:text class="text-sm text-zinc-500 mt-1">
                                Duration: {{ $event->formatted_duration }}
                            </flux:text>
                        </div>

                        {{-- Location --}}
                        @if($event->location)
                            <div>
                                <flux:text class="font-medium text-zinc-900 flex items-center mb-2">
                                    <flux:icon name="map-pin" class="w-5 h-5 mr-2" />
                                    Location
                                </flux:text>
                                <flux:text class="text-zinc-600">
                                    {{ $event->location }}
                                </flux:text>
                            </div>
                        @endif

                        {{-- Author --}}
                        @if($event->author)
                            <div>
                                <flux:text class="font-medium text-zinc-900 flex items-center mb-2">
                                    <flux:icon name="user" class="w-5 h-5 mr-2" />
                                    Created by
                                </flux:text>
                                <flux:text class="text-zinc-600">
                                    {{ $event->author->name }}
                                </flux:text>
                            </div>
                        @endif

                        {{-- Import Source --}}
                        @if($event->eventFeed)
                            <div>
                                <flux:text class="font-medium text-zinc-900 flex items-center mb-2">
                                    <flux:icon name="rss" class="w-5 h-5 mr-2" />
                                    Imported from
                                </flux:text>
                                <flux:text class="text-zinc-600">
                                    {{ $event->eventFeed->name }}
                                </flux:text>
                            </div>
                        @endif

                        {{-- Tags --}}
                        @if($event->tags)
                            <div>
                                <flux:text class="font-medium text-zinc-900 flex items-center mb-2">
                                    <flux:icon name="tag" class="w-5 h-5 mr-2" />
                                    Tags
                                </flux:text>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($event->tags as $tag)
                                        <flux:badge variant="soft" size="sm">{{ $tag }}</flux:badge>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>

            {{-- Quick Actions --}}
            <flux:card>
                <div class="p-6">
                    <flux:heading size="lg" class="mb-4">Quick Actions</flux:heading>
                    <div class="space-y-3">
                        @if($event->info_url)
                            <flux:button variant="outline" href="{{ $event->info_url }}" target="_blank" class="w-full justify-center">
                                <flux:icon name="arrow-top-right-on-square" class="w-4 h-4 mr-2" />
                                View Full Details
                            </flux:button>
                        @endif

                        {{-- Add to Calendar (future enhancement) --}}
                        <flux:button variant="outline" class="w-full justify-center" disabled>
                            <flux:icon name="calendar-plus" class="w-4 h-4 mr-2" />
                            Add to Calendar
                        </flux:button>

                        {{-- Share (future enhancement) --}}
                        <flux:button variant="outline" class="w-full justify-center" disabled>
                            <flux:icon name="share" class="w-4 h-4 mr-2" />
                            Share Event
                        </flux:button>
                    </div>
                </div>
            </flux:card>

            {{-- Related Events --}}
            @php
                $relatedEvents = $team->events()
                    ->published()
                    ->where('id', '!=', $event->id)
                    ->where(function($query) use ($event) {
                        // Find events with overlapping tags or similar dates
                        if ($event->tags) {
                            foreach ($event->tags as $tag) {
                                $query->orWhereJsonContains('tags', $tag);
                            }
                        }
                        $query->orWhereBetween('start_datetime', [
                            $event->start_datetime->subDays(7),
                            $event->start_datetime->addDays(7)
                        ]);
                    })
                    ->orderBy('start_datetime', 'asc')
                    ->limit(3)
                    ->get();
            @endphp

            @if($relatedEvents->count() > 0)
                <flux:card>
                    <div class="p-6">
                        <flux:heading size="lg" class="mb-4">Related Events</flux:heading>
                        <div class="space-y-3">
                            @foreach($relatedEvents as $relatedEvent)
                                <div class="p-3 border border-zinc-200 rounded-lg hover:bg-zinc-50 transition-colors">
                                    <a href="{{ route('calendar.event', [$team->slug, $relatedEvent->slug]) }}" class="block">
                                        <flux:text class="font-medium text-zinc-900 text-sm mb-1">
                                            {{ $relatedEvent->title }}
                                        </flux:text>
                                        <flux:text class="text-xs text-zinc-500">
                                            {{ $relatedEvent->start_datetime->format('M j, Y g:i A') }}
                                        </flux:text>
                                        @if($relatedEvent->location)
                                            <flux:text class="text-xs text-zinc-400 mt-1">
                                                {{ $relatedEvent->location }}
                                            </flux:text>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <flux:button variant="ghost" href="{{ route('calendar.team', $team->slug) }}" class="w-full justify-center text-sm">
                                View All Events
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            @endif
        </div>
    </div>
</div>
@endsection