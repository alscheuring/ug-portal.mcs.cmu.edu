<x-layouts.public :title="$team->name . ' Events'" :team="$team">
    @push('styles')
        <style>
            /* FullCalendar custom styles */
            .fc-theme-standard .fc-scrollgrid {
                border: 1px solid #e5e7eb;
            }

            .fc-theme-standard th,
            .fc-theme-standard td {
                border: 1px solid #f3f4f6;
            }

            .fc-theme-standard .fc-scrollgrid-section > * {
                border-width: 0;
            }

            .fc .fc-daygrid-event {
                font-size: 0.85em;
                border-radius: 4px;
                margin-bottom: 1px;
            }

            .fc-event-location {
                margin-top: 2px;
                font-style: italic;
            }

            .fc-toolbar-title {
                font-size: 1.5rem !important;
                font-weight: 700;
                color: #111827;
            }

            .fc-button-primary {
                background-color: #3B82F6;
                border-color: #3B82F6;
            }

            .fc-button-primary:not(:disabled):active,
            .fc-button-primary:not(:disabled).fc-button-active {
                background-color: #1D4ED8;
                border-color: #1D4ED8;
            }

            .fc-button-primary:hover {
                background-color: #2563EB;
                border-color: #2563EB;
            }

            #calendar-loading {
                text-align: center;
                padding: 20px;
                color: #6B7280;
            }
        </style>
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $team->name }} Events</h1>
            <p class="text-lg text-gray-600">View and manage events for the {{ $team->name }} team</p>
        </div>

        <!-- Calendar Controls -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="flex flex-wrap gap-2">
                    <span class="text-sm text-gray-600">Legend:</span>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-1"></span>
                        Academic Events
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                        <span class="w-2 h-2 bg-gray-500 rounded-full mr-1"></span>
                        Imported Events
                    </span>
                </div>

                <div class="text-sm text-gray-500">
                    Click on events to view details
                </div>
            </div>
        </div>

        <!-- Calendar Container -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div id="calendar-loading" style="display: none;">
                <div class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading events...
                </div>
            </div>

            <div id="calendar"
                 data-team-slug="{{ $team->slug }}"
                 class="p-4">
            </div>
        </div>

        <!-- Event Stats -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white shadow-sm rounded-lg p-6 text-center">
                <div class="text-2xl font-bold text-blue-600 mb-2" id="upcoming-count">-</div>
                <div class="text-sm text-gray-600">Upcoming Events</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6 text-center">
                <div class="text-2xl font-bold text-green-600 mb-2" id="happening-count">-</div>
                <div class="text-sm text-gray-600">Happening Now</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6 text-center">
                <div class="text-2xl font-bold text-gray-600 mb-2" id="total-count">-</div>
                <div class="text-sm text-gray-600">Total Events</div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Update event counts when calendar loads
            document.addEventListener('DOMContentLoaded', function() {
                const teamSlug = '{{ $team->slug }}';

                // Fetch event statistics
                fetch(`/${teamSlug}/calendar/events?upcoming=1`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('upcoming-count').textContent = data.length;
                    });

                fetch(`/${teamSlug}/calendar/events?happening=1`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('happening-count').textContent = data.length;
                    });

                fetch(`/${teamSlug}/calendar/events`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('total-count').textContent = data.length;
                    });
            });
        </script>
    @endpush
</x-layouts.public>
