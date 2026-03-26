<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class TeamCalendar extends Component
{
    public Team $team;

    public string $currentMonth;

    public string $view = 'month';

    public string $search = '';

    public function mount(Team $team): void
    {
        $this->team = $team;
        $this->currentMonth = now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromFormat('Y-m', $this->currentMonth)->subMonth();
        $this->currentMonth = $date->format('Y-m');
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromFormat('Y-m', $this->currentMonth)->addMonth();
        $this->currentMonth = $date->format('Y-m');
    }

    public function today(): void
    {
        $this->currentMonth = now()->format('Y-m');
    }

    public function getCalendarDataProperty(): array
    {
        $currentDate = Carbon::createFromFormat('Y-m', $this->currentMonth);
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        // Get start of calendar (might be previous month to fill the grid)
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();

        // Get end of calendar (might be next month to fill the grid)
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();

        // Get all events in the calendar view period
        $events = Event::query()
            ->where('team_id', $this->team->id)
            ->where('is_published', true)
            ->whereBetween('start_datetime', [
                $startOfCalendar->copy()->startOfDay(),
                $endOfCalendar->copy()->endOfDay(),
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhere('location', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('start_datetime')
            ->get();

        // Group events by date
        $eventsByDate = $events->groupBy(function ($event) {
            return $event->start_datetime->format('Y-m-d');
        });

        // Generate calendar grid
        $weeks = [];
        $currentWeekStart = $startOfCalendar->copy();

        while ($currentWeekStart->lte($endOfCalendar)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $day = $currentWeekStart->copy()->addDays($i);
                $dayEvents = $eventsByDate->get($day->format('Y-m-d'), collect());

                $week[] = [
                    'date' => $day,
                    'isCurrentMonth' => $day->month === $currentDate->month,
                    'isToday' => $day->isToday(),
                    'events' => $dayEvents,
                ];
            }

            $weeks[] = $week;
            $currentWeekStart->addWeek();
        }

        return [
            'currentDate' => $currentDate,
            'weeks' => $weeks,
            'monthName' => $currentDate->format('F Y'),
        ];
    }

    public function render(): View
    {
        return view('livewire.team-calendar');
    }
}
