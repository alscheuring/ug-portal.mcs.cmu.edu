<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Display the team calendar page.
     */
    public function teamCalendar(Team $team): View
    {
        // Ensure the team is active
        if (! $team->is_active) {
            abort(404);
        }

        return view('calendar.team', compact('team'));
    }

    /**
     * Get calendar events data for a team.
     */
    public function teamEvents(Team $team, Request $request): JsonResponse
    {
        // Ensure the team is active
        if (! $team->is_active) {
            abort(404);
        }

        $query = Event::query()
            ->where('team_id', $team->id)
            ->published()
            ->with(['author', 'eventFeed']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get events
        $events = $query->orderBy('start_datetime', 'asc')->get();

        // Format events for calendar display
        $formattedEvents = $events->map(function (Event $event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_datetime->toISOString(),
                'end' => $event->end_datetime->toISOString(),
                'allDay' => $this->isAllDayEvent($event),
                'url' => $event->url,
                'description' => $event->summary ?: $event->description,
                'location' => $event->location,
                'tags' => $event->tags ?? [],
                'source_type' => $event->source_type,
                'backgroundColor' => $this->getEventColor($event),
                'borderColor' => $this->getEventColor($event),
                'extendedProps' => [
                    'summary' => $event->summary,
                    'description' => $event->description,
                    'location' => $event->location,
                    'info_url' => $event->info_url,
                    'image_url' => $event->image_url,
                    'tags' => $event->tags ?? [],
                    'source_type' => $event->source_type,
                    'author' => $event->author?->name,
                    'feed_name' => $event->eventFeed?->name,
                    'formatted_date_range' => $event->formatted_date_range,
                    'formatted_duration' => $event->formatted_duration,
                    'is_happening' => $event->isHappening(),
                    'is_upcoming' => $event->isUpcoming(),
                    'is_past' => $event->isPast(),
                ],
            ];
        });

        return response()->json($formattedEvents);
    }

    /**
     * Get calendar events for all teams (public feed).
     */
    public function globalEvents(Request $request): JsonResponse
    {
        $query = Event::query()
            ->published()
            ->whereHas('team', fn ($q) => $q->where('is_active', true))
            ->with(['team', 'author', 'eventFeed']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get events
        $events = $query->orderBy('start_datetime', 'asc')->get();

        // Format events for calendar display
        $formattedEvents = $events->map(function (Event $event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_datetime->toISOString(),
                'end' => $event->end_datetime->toISOString(),
                'allDay' => $this->isAllDayEvent($event),
                'url' => $event->url,
                'description' => $event->summary ?: $event->description,
                'location' => $event->location,
                'tags' => $event->tags ?? [],
                'source_type' => $event->source_type,
                'backgroundColor' => $this->getEventColor($event),
                'borderColor' => $this->getEventColor($event),
                'extendedProps' => [
                    'team_name' => $event->team->name,
                    'team_slug' => $event->team->slug,
                    'summary' => $event->summary,
                    'description' => $event->description,
                    'location' => $event->location,
                    'info_url' => $event->info_url,
                    'image_url' => $event->image_url,
                    'tags' => $event->tags ?? [],
                    'source_type' => $event->source_type,
                    'author' => $event->author?->name,
                    'feed_name' => $event->eventFeed?->name,
                    'formatted_date_range' => $event->formatted_date_range,
                    'formatted_duration' => $event->formatted_duration,
                    'is_happening' => $event->isHappening(),
                    'is_upcoming' => $event->isUpcoming(),
                    'is_past' => $event->isPast(),
                ],
            ];
        });

        return response()->json($formattedEvents);
    }

    /**
     * Apply filters to the events query.
     */
    protected function applyFilters($query, Request $request): void
    {
        // Date range filter
        if ($request->has('start') && $request->has('end')) {
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);
            $query->dateRange($start, $end);
        }

        // Upcoming events only
        if ($request->boolean('upcoming')) {
            $query->upcoming();
        }

        // Past events only
        if ($request->boolean('past')) {
            $query->past();
        }

        // Happening now
        if ($request->boolean('happening')) {
            $query->where('start_datetime', '<=', now())
                ->where('end_datetime', '>=', now());
        }

        // Filter by source type
        if ($request->has('source_type')) {
            $query->bySourceType($request->source_type);
        }

        // Filter by tags
        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            foreach ($tags as $tag) {
                $query->withTag(trim($tag));
            }
        }

        // Search in title and description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Limit results
        $limit = $request->integer('limit', 100);
        $query->limit(min($limit, 500)); // Max 500 events
    }

    /**
     * Determine if an event is all-day.
     */
    protected function isAllDayEvent(Event $event): bool
    {
        return $event->start_datetime->format('H:i:s') === '00:00:00' &&
               $event->end_datetime->format('H:i:s') === '23:59:59';
    }

    /**
     * Get color for event based on tags or source type.
     */
    protected function getEventColor(Event $event): string
    {
        // Colors based on source type
        if ($event->source_type === 'imported') {
            return '#6B7280'; // Gray for imported events
        }

        // Colors based on tags
        $tags = $event->tags ?? [];

        $tagColors = [
            'academic' => '#3B82F6', // Blue
            'research' => '#8B5CF6', // Purple
            'seminar' => '#10B981', // Green
            'workshop' => '#F59E0B', // Yellow
            'conference' => '#EF4444', // Red
            'social' => '#EC4899', // Pink
            'networking' => '#06B6D4', // Cyan
            'graduation' => '#8B5CF6', // Purple
            'orientation' => '#10B981', // Green
            'meeting' => '#6B7280', // Gray
            'lecture' => '#3B82F6', // Blue
            'presentation' => '#8B5CF6', // Purple
            'competition' => '#EF4444', // Red
            'career' => '#059669', // Emerald
            'volunteer' => '#DC2626', // Red
        ];

        // Return color for first matching tag
        foreach ($tags as $tag) {
            if (isset($tagColors[$tag])) {
                return $tagColors[$tag];
            }
        }

        // Default color for manual events
        return '#3B82F6'; // Blue
    }

    /**
     * Show individual event details.
     */
    public function showEvent(Team $team, Event $event): View
    {
        // Ensure the event belongs to the team and is published
        if ($event->team_id !== $team->id || ! $event->is_published) {
            abort(404);
        }

        return view('calendar.event', compact('team', 'event'));
    }
}
