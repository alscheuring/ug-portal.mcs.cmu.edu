<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
            'start_datetime' => $this->start_datetime->toISOString(),
            'end_datetime' => $this->end_datetime->toISOString(),
            'location' => $this->location,
            'info_url' => $this->info_url,
            'image_url' => $this->image_url,
            'tags' => $this->tags ?? [],
            'is_published' => $this->is_published,
            'source_type' => $this->source_type,
            'external_id' => $this->external_id,

            // Computed attributes
            'url' => url($this->url),
            'formatted_date_range' => $this->formatted_date_range,
            'formatted_duration' => $this->formatted_duration,
            'is_happening' => $this->isHappening(),
            'is_upcoming' => $this->isUpcoming(),
            'is_past' => $this->isPast(),
            'duration_in_hours' => $this->getDurationInHours(),

            // Relationships
            'team' => $this->whenLoaded('team', function () {
                return [
                    'id' => $this->team->id,
                    'name' => $this->team->name,
                    'slug' => $this->team->slug,
                    'description' => $this->team->description,
                    'is_active' => $this->team->is_active,
                ];
            }),

            'author' => $this->whenLoaded('author', function () {
                return $this->author ? [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                    'initials' => $this->author->initials(),
                ] : null;
            }),

            'event_feed' => $this->whenLoaded('eventFeed', function () {
                return $this->eventFeed ? [
                    'id' => $this->eventFeed->id,
                    'name' => $this->eventFeed->name,
                    'api_url' => $this->eventFeed->api_url,
                    'is_active' => $this->eventFeed->is_active,
                ] : null;
            }),

            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Calendar-specific data (when requested)
            'calendar' => $this->when($request->routeIs('api.events.calendar'), function () {
                return [
                    'all_day' => $this->isAllDayEvent(),
                    'color' => $this->getEventColor(),
                    'text_color' => '#ffffff',
                ];
            }),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timezone' => config('app.timezone'),
                'generated_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Determine if an event is all-day.
     */
    protected function isAllDayEvent(): bool
    {
        return $this->start_datetime->format('H:i:s') === '00:00:00' &&
               $this->end_datetime->format('H:i:s') === '23:59:59';
    }

    /**
     * Get color for event based on tags or source type.
     */
    protected function getEventColor(): string
    {
        // Colors based on source type
        if ($this->source_type === 'imported') {
            return '#6B7280'; // Gray for imported events
        }

        // Colors based on tags
        $tags = $this->tags ?? [];

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
