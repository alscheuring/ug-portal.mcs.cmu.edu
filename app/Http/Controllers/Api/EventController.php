<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EventController extends Controller
{
    /**
     * Get all published events across all teams.
     */
    public function index(Request $request): ResourceCollection
    {
        $query = Event::query()
            ->published()
            ->whereHas('team', fn ($q) => $q->where('is_active', true))
            ->with(['team', 'author', 'eventFeed']);

        $this->applyFilters($query, $request);

        $events = $query->orderBy('start_datetime', 'asc')
            ->paginate($request->integer('per_page', 20));

        return EventResource::collection($events);
    }

    /**
     * Get events for a specific team.
     */
    public function teamEvents(Team $team, Request $request): ResourceCollection
    {
        // Ensure the team is active
        if (! $team->is_active) {
            abort(404, 'Team not found or inactive');
        }

        $query = Event::query()
            ->where('team_id', $team->id)
            ->published()
            ->with(['author', 'eventFeed']);

        $this->applyFilters($query, $request);

        $events = $query->orderBy('start_datetime', 'asc')
            ->paginate($request->integer('per_page', 20));

        return EventResource::collection($events);
    }

    /**
     * Get a specific event.
     */
    public function show(Event $event): EventResource
    {
        // Ensure the event is published and belongs to an active team
        if (! $event->is_published || ! $event->team->is_active) {
            abort(404, 'Event not found');
        }

        $event->load(['team', 'author', 'eventFeed']);

        return new EventResource($event);
    }

    /**
     * Get upcoming events across all teams.
     */
    public function upcoming(Request $request): ResourceCollection
    {
        $query = Event::query()
            ->published()
            ->upcoming()
            ->whereHas('team', fn ($q) => $q->where('is_active', true))
            ->with(['team', 'author', 'eventFeed']);

        $this->applyFilters($query, $request, ['upcoming' => false]); // Skip upcoming filter since we already applied it

        $events = $query->orderBy('start_datetime', 'asc')
            ->paginate($request->integer('per_page', 20));

        return EventResource::collection($events);
    }

    /**
     * Get events happening today across all teams.
     */
    public function today(Request $request): ResourceCollection
    {
        $query = Event::query()
            ->published()
            ->whereDate('start_datetime', today())
            ->whereHas('team', fn ($q) => $q->where('is_active', true))
            ->with(['team', 'author', 'eventFeed']);

        $this->applyFilters($query, $request, ['date_filter' => false]); // Skip date filters

        $events = $query->orderBy('start_datetime', 'asc')->get();

        return EventResource::collection($events);
    }

    /**
     * Get events happening this week across all teams.
     */
    public function thisWeek(Request $request): ResourceCollection
    {
        $query = Event::query()
            ->published()
            ->whereBetween('start_datetime', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->whereHas('team', fn ($q) => $q->where('is_active', true))
            ->with(['team', 'author', 'eventFeed']);

        $this->applyFilters($query, $request, ['date_filter' => false]); // Skip date filters

        $events = $query->orderBy('start_datetime', 'asc')->get();

        return EventResource::collection($events);
    }

    /**
     * Get events happening this month across all teams.
     */
    public function thisMonth(Request $request): ResourceCollection
    {
        $query = Event::query()
            ->published()
            ->whereBetween('start_datetime', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->whereHas('team', fn ($q) => $q->where('is_active', true))
            ->with(['team', 'author', 'eventFeed']);

        $this->applyFilters($query, $request, ['date_filter' => false]); // Skip date filters

        $events = $query->orderBy('start_datetime', 'asc')->get();

        return EventResource::collection($events);
    }

    /**
     * Get calendar feed data for external calendar applications.
     */
    public function calendar(Request $request): JsonResponse
    {
        $query = Event::query()
            ->published()
            ->whereHas('team', fn ($q) => $q->where('is_active', true))
            ->with(['team', 'author', 'eventFeed']);

        // Default to next 6 months if no date range specified
        if (! $request->has('start') && ! $request->has('end')) {
            $query->whereBetween('start_datetime', [
                now()->startOfDay(),
                now()->addMonths(6)->endOfDay(),
            ]);
        }

        $this->applyFilters($query, $request);

        $events = $query->orderBy('start_datetime', 'asc')
            ->limit($request->integer('limit', 500))
            ->get();

        // Format events for calendar applications
        $calendarEvents = $events->map(function (Event $event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_datetime->toISOString(),
                'end' => $event->end_datetime->toISOString(),
                'allDay' => $this->isAllDayEvent($event),
                'url' => url($event->url),
                'description' => $event->summary ?: $event->description,
                'location' => $event->location,
                'backgroundColor' => $this->getEventColor($event),
                'borderColor' => $this->getEventColor($event),
                'extendedProps' => [
                    'team' => $event->team->name,
                    'team_slug' => $event->team->slug,
                    'summary' => $event->summary,
                    'tags' => $event->tags ?? [],
                    'source_type' => $event->source_type,
                    'author' => $event->author?->name,
                    'feed_name' => $event->eventFeed?->name,
                    'info_url' => $event->info_url,
                    'image_url' => $event->image_url,
                ],
            ];
        });

        return response()->json($calendarEvents);
    }

    /**
     * Get events statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $baseQuery = Event::query()
            ->published()
            ->whereHas('team', fn ($q) => $q->where('is_active', true));

        // Apply team filter if specified
        if ($request->has('team')) {
            $team = Team::where('slug', $request->team)->firstOrFail();
            $baseQuery->where('team_id', $team->id);
        }

        $stats = [
            'total_events' => (clone $baseQuery)->count(),
            'upcoming_events' => (clone $baseQuery)->upcoming()->count(),
            'events_this_week' => (clone $baseQuery)->whereBetween('start_datetime', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'events_this_month' => (clone $baseQuery)->whereBetween('start_datetime', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])->count(),
            'manual_events' => (clone $baseQuery)->manual()->count(),
            'imported_events' => (clone $baseQuery)->imported()->count(),
            'events_by_team' => Event::query()
                ->published()
                ->whereHas('team', fn ($q) => $q->where('is_active', true))
                ->selectRaw('teams.name as team_name, teams.slug as team_slug, COUNT(*) as event_count')
                ->join('teams', 'events.team_id', '=', 'teams.id')
                ->groupBy('teams.id', 'teams.name', 'teams.slug')
                ->orderBy('event_count', 'desc')
                ->get()
                ->toArray(),
            'popular_tags' => Event::query()
                ->published()
                ->whereHas('team', fn ($q) => $q->where('is_active', true))
                ->whereNotNull('tags')
                ->where('tags', '!=', '[]')
                ->get()
                ->pluck('tags')
                ->flatten()
                ->countBy()
                ->sortDesc()
                ->take(10)
                ->toArray(),
        ];

        return response()->json($stats);
    }

    /**
     * Apply filters to the event query.
     */
    protected function applyFilters($query, Request $request, array $skipFilters = []): void
    {
        // Date range filter
        if (! in_array('date_filter', $skipFilters) && ($request->has('start') || $request->has('end'))) {
            if ($request->has('start') && $request->has('end')) {
                $start = Carbon::parse($request->start);
                $end = Carbon::parse($request->end);
                $query->dateRange($start, $end);
            } elseif ($request->has('start')) {
                $start = Carbon::parse($request->start);
                $query->where('start_datetime', '>=', $start);
            } elseif ($request->has('end')) {
                $end = Carbon::parse($request->end);
                $query->where('end_datetime', '<=', $end);
            }
        }

        // Upcoming events filter
        if (! in_array('upcoming', $skipFilters) && $request->boolean('upcoming')) {
            $query->upcoming();
        }

        // Past events filter
        if ($request->boolean('past')) {
            $query->past();
        }

        // Happening now filter
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

        // Search in title, description, and location
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by team (when not already scoped)
        if ($request->has('team') && ! $request->routeIs('api.events.team.*')) {
            $team = Team::where('slug', $request->team)->firstOrFail();
            $query->where('team_id', $team->id);
        }
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
            'academic' => '#3B82F6',
            'research' => '#8B5CF6',
            'seminar' => '#10B981',
            'workshop' => '#F59E0B',
            'conference' => '#EF4444',
            'social' => '#EC4899',
            'networking' => '#06B6D4',
            'graduation' => '#8B5CF6',
            'orientation' => '#10B981',
            'meeting' => '#6B7280',
            'lecture' => '#3B82F6',
            'presentation' => '#8B5CF6',
            'competition' => '#EF4444',
            'career' => '#059669',
            'volunteer' => '#DC2626',
        ];

        foreach ($tags as $tag) {
            if (isset($tagColors[$tag])) {
                return $tagColors[$tag];
            }
        }

        return '#3B82F6';
    }
}
