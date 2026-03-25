<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TeamCalendar extends Component
{
    public Team $team;

    // Current view date
    public string $currentDate;

    public string $view = 'month'; // month, week, day, agenda

    // Filters
    public string $search = '';

    public array $selectedTags = [];

    public string $sourceTypeFilter = 'all'; // all, manual, imported

    public ?string $dateRangeStart = null;

    public ?string $dateRangeEnd = null;

    // Event creation modal
    public bool $showCreateModal = false;

    public array $newEvent = [
        'title' => '',
        'description' => '',
        'summary' => '',
        'start_datetime' => '',
        'end_datetime' => '',
        'location' => '',
        'info_url' => '',
        'image_url' => '',
        'tags' => [],
        'is_published' => true,
    ];

    // Event detail modal
    public bool $showEventModal = false;

    public ?Event $selectedEvent = null;

    // Available tags for suggestions
    public array $availableTags = [
        'academic', 'research', 'seminar', 'workshop', 'conference',
        'social', 'networking', 'graduation', 'orientation', 'meeting',
        'lecture', 'presentation', 'competition', 'career', 'volunteer',
        'student', 'faculty', 'undergraduate', 'graduate', 'phd',
    ];

    public function mount(Team $team): void
    {
        $this->team = $team;
        $this->currentDate = now()->format('Y-m-d');

        // Set default date range to current month
        $this->dateRangeStart = now()->startOfMonth()->format('Y-m-d');
        $this->dateRangeEnd = now()->endOfMonth()->format('Y-m-d');
    }

    #[Computed]
    public function events()
    {
        $query = Event::query()
            ->where('team_id', $this->team->id)
            ->published()
            ->with(['author', 'eventFeed']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhere('summary', 'like', "%{$this->search}%")
                    ->orWhere('location', 'like', "%{$this->search}%");
            });
        }

        // Apply tag filters
        if (! empty($this->selectedTags)) {
            foreach ($this->selectedTags as $tag) {
                $query->withTag($tag);
            }
        }

        // Apply source type filter
        if ($this->sourceTypeFilter !== 'all') {
            $query->bySourceType($this->sourceTypeFilter);
        }

        // Apply date range filter
        if ($this->dateRangeStart && $this->dateRangeEnd) {
            $query->dateRange(
                Carbon::parse($this->dateRangeStart)->startOfDay(),
                Carbon::parse($this->dateRangeEnd)->endOfDay()
            );
        }

        return $query->orderBy('start_datetime', 'asc')->get();
    }

    #[Computed]
    public function canCreateEvents(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // TeamAdmins can create events for their team
        return $user->isTeamAdmin() || $user->isSuperAdmin();
    }

    #[Computed]
    public function currentMonth(): string
    {
        return Carbon::parse($this->currentDate)->format('F Y');
    }

    #[Computed]
    public function calendarEvents(): array
    {
        return $this->events->map(function (Event $event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_datetime->toISOString(),
                'end' => $event->end_datetime->toISOString(),
                'allDay' => $this->isAllDayEvent($event),
                'backgroundColor' => $this->getEventColor($event),
                'borderColor' => $this->getEventColor($event),
                'textColor' => '#ffffff',
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
        })->toArray();
    }

    public function previousMonth(): void
    {
        $this->currentDate = Carbon::parse($this->currentDate)
            ->subMonth()
            ->format('Y-m-d');

        $this->updateDateRange();
    }

    public function nextMonth(): void
    {
        $this->currentDate = Carbon::parse($this->currentDate)
            ->addMonth()
            ->format('Y-m-d');

        $this->updateDateRange();
    }

    public function goToToday(): void
    {
        $this->currentDate = now()->format('Y-m-d');
        $this->updateDateRange();
    }

    public function changeView(string $view): void
    {
        $this->view = $view;
        $this->updateDateRange();
    }

    protected function updateDateRange(): void
    {
        $date = Carbon::parse($this->currentDate);

        match ($this->view) {
            'month' => [
                $this->dateRangeStart = $date->copy()->startOfMonth()->format('Y-m-d'),
                $this->dateRangeEnd = $date->copy()->endOfMonth()->format('Y-m-d'),
            ],
            'week' => [
                $this->dateRangeStart = $date->copy()->startOfWeek()->format('Y-m-d'),
                $this->dateRangeEnd = $date->copy()->endOfWeek()->format('Y-m-d'),
            ],
            'day' => [
                $this->dateRangeStart = $date->format('Y-m-d'),
                $this->dateRangeEnd = $date->format('Y-m-d'),
            ],
            default => null,
        };
    }

    public function addTag(string $tag): void
    {
        if (! in_array($tag, $this->selectedTags)) {
            $this->selectedTags[] = $tag;
        }
    }

    public function removeTag(string $tag): void
    {
        $this->selectedTags = array_filter($this->selectedTags, fn ($t) => $t !== $tag);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedTags = [];
        $this->sourceTypeFilter = 'all';
        $this->dateRangeStart = now()->startOfMonth()->format('Y-m-d');
        $this->dateRangeEnd = now()->endOfMonth()->format('Y-m-d');
    }

    public function openCreateModal(): void
    {
        if (! $this->canCreateEvents) {
            return;
        }

        $this->resetNewEvent();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetNewEvent();
        $this->resetValidation();
    }

    protected function resetNewEvent(): void
    {
        $this->newEvent = [
            'title' => '',
            'description' => '',
            'summary' => '',
            'start_datetime' => '',
            'end_datetime' => '',
            'location' => '',
            'info_url' => '',
            'image_url' => '',
            'tags' => [],
            'is_published' => true,
        ];
    }

    public function createEvent(): void
    {
        if (! $this->canCreateEvents) {
            return;
        }

        $this->validate([
            'newEvent.title' => 'required|string|max:255',
            'newEvent.start_datetime' => 'required|date',
            'newEvent.end_datetime' => 'required|date|after:newEvent.start_datetime',
            'newEvent.location' => 'nullable|string|max:255',
            'newEvent.info_url' => 'nullable|url|max:255',
            'newEvent.image_url' => 'nullable|url|max:255',
            'newEvent.summary' => 'nullable|string|max:500',
            'newEvent.description' => 'nullable|string',
        ]);

        Event::create([
            'title' => $this->newEvent['title'],
            'slug' => Event::generateSlug($this->newEvent['title']),
            'description' => $this->newEvent['description'],
            'summary' => $this->newEvent['summary'],
            'start_datetime' => $this->newEvent['start_datetime'],
            'end_datetime' => $this->newEvent['end_datetime'],
            'location' => $this->newEvent['location'],
            'info_url' => $this->newEvent['info_url'],
            'image_url' => $this->newEvent['image_url'],
            'tags' => $this->newEvent['tags'],
            'is_published' => $this->newEvent['is_published'],
            'source_type' => 'manual',
            'team_id' => $this->team->id,
            'author_id' => auth()->id(),
        ]);

        $this->closeCreateModal();

        $this->dispatch('event-created', [
            'message' => 'Event created successfully!',
        ]);
    }

    public function showEvent(int $eventId): void
    {
        $this->selectedEvent = Event::find($eventId);
        $this->showEventModal = true;
    }

    public function closeEventModal(): void
    {
        $this->showEventModal = false;
        $this->selectedEvent = null;
    }

    #[On('event-clicked')]
    public function handleEventClick($eventId): void
    {
        $this->showEvent($eventId);
    }

    protected function isAllDayEvent(Event $event): bool
    {
        return $event->start_datetime->format('H:i:s') === '00:00:00' &&
               $event->end_datetime->format('H:i:s') === '23:59:59';
    }

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

    public function render(): View
    {
        return view('livewire.team-calendar');
    }
}
