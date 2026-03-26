import './bootstrap';

// FullCalendar imports
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        const teamSlug = calendarEl.dataset.teamSlug;

        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: `/${teamSlug}/calendar/events`,
            eventDisplay: 'block',
            eventClick: function(info) {
                // Prevent default link behavior if we want to show modal
                info.jsEvent.preventDefault();

                // Show event details modal or navigate to event page
                if (info.event.url) {
                    window.location.href = info.event.url;
                } else {
                    showEventModal(info.event);
                }
            },
            eventDidMount: function(info) {
                // Add tooltips or additional styling
                info.el.title = info.event.extendedProps.description || info.event.title;

                // Add location if available
                if (info.event.extendedProps.location) {
                    const locationEl = document.createElement('div');
                    locationEl.className = 'fc-event-location';
                    locationEl.textContent = info.event.extendedProps.location;
                    locationEl.style.fontSize = '0.8em';
                    locationEl.style.opacity = '0.8';
                    info.el.appendChild(locationEl);
                }
            },
            loading: function(isLoading) {
                // Show/hide loading indicator
                const loadingEl = document.getElementById('calendar-loading');
                if (loadingEl) {
                    loadingEl.style.display = isLoading ? 'block' : 'none';
                }
            }
        });

        calendar.render();
    }
});

// Function to show event modal (optional)
function showEventModal(event) {
    // Simple alert for now - could be enhanced with a proper modal
    let details = `${event.title}\n\n`;
    if (event.extendedProps.location) {
        details += `Location: ${event.extendedProps.location}\n`;
    }
    if (event.extendedProps.description) {
        details += `Description: ${event.extendedProps.description}\n`;
    }
    details += `Time: ${event.start.toLocaleDateString()} ${event.start.toLocaleTimeString()}`;
    if (event.end) {
        details += ` - ${event.end.toLocaleTimeString()}`;
    }

    alert(details);
}