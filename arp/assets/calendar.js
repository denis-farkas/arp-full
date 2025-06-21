// assets/calendar.js - Version compatible Turbo
import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import frLocale from "@fullcalendar/core/locales/fr";

import "./styles/calendar.css";

let calendarInstance = null;

function initializeCalendar() {
  const calendarEl = document.getElementById("calendar-holder");

  // Guard clause to prevent errors if element doesn't exist
  if (!calendarEl) {
    console.warn("Calendar element #calendar-holder not found on page");
    return;
  }

  // Destroy existing calendar instance if it exists
  if (calendarInstance) {
    try {
      calendarInstance.destroy();
      calendarInstance = null;
    } catch (error) {
      console.warn("Error destroying previous calendar instance:", error);
    }
  }

  // Get events URL from data attribute or use default
  const eventsUrl = calendarEl.dataset.eventsUrl || "/load-events";

  try {
    calendarInstance = new Calendar(calendarEl, {
      initialView: "timeGridWeek",
      editable: false,
      events: eventsUrl,
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
      },
      timeZone: "Europe/Paris",
      plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin],
      locale: frLocale,
      weekends: true,
      businessHours: [
        {
          daysOfWeek: [1, 2, 3, 4, 5, 6, 7],
          startTime: "09:00",
          endTime: "12:00",
        },
        {
          daysOfWeek: [1, 2, 3, 4, 5, 6, 7],
          startTime: "13:00",
          endTime: "19:00",
        },
        {
          daysOfWeek: [1, 2, 3, 4, 5, 6, 7],
          startTime: "20:00",
          endTime: "22:00",
        },
      ],
      selectConstraint: "businessHours",
      slotMinTime: "09:00:00",
      slotMaxTime: "22:00:00",
      validRange: {
        start: new Date().toISOString().split("T")[0],
      },
      eventDidMount: function (info) {
        // Add a custom class for the user's own courses
        if (info.event.extendedProps && info.event.extendedProps.isUserRdv) {
          info.el.classList.add("user-rdv");
        }

        // Add tooltip with appointment information
        const tooltipContent = `
          <div>
            <strong>${info.event.title}</strong><br>
            Début: ${new Date(info.event.start).toLocaleTimeString("fr-FR", {
              hour: "2-digit",
              minute: "2-digit",
            })}<br>
            Fin: ${new Date(info.event.end).toLocaleTimeString("fr-FR", {
              hour: "2-digit",
              minute: "2-digit",
            })}
          </div>
        `;

        info.el.title = info.event.title;
        info.el.style.cursor = "pointer";
      },
    });

    // Render the calendar
    calendarInstance.render();

    // Add legend if not exists
    const existingLegend = document.querySelector(".calendar-legend");
    if (!existingLegend && calendarEl.parentNode) {
      const legendEl = document.createElement("div");
      legendEl.className = "calendar-legend";
      legendEl.innerHTML = `
        <div class="legend-item">
          <span class="color-box" style="background-color: #3788d8;"></span>
          <span>Vos rendez-vous</span>
        </div>
        <div class="legend-item">
          <span class="color-box" style="background-color: #6c757d;"></span>
          <span>Rendez-vous des autres</span>
        </div>
      `;
      calendarEl.parentNode.insertBefore(legendEl, calendarEl);
    }

    console.log("Calendar initialized successfully");
  } catch (error) {
    console.error("Error initializing calendar:", error);
  }
}

// Clean up calendar instance
function cleanupCalendar() {
  if (calendarInstance) {
    try {
      calendarInstance.destroy();
      calendarInstance = null;
    } catch (error) {
      console.warn("Error cleaning up calendar:", error);
    }
  }

  // Remove legend
  const legend = document.querySelector(".calendar-legend");
  if (legend) {
    legend.remove();
  }
}

// Initialize on DOM content loaded
document.addEventListener("DOMContentLoaded", initializeCalendar);

// Handle Turbo navigation
document.addEventListener("turbo:load", initializeCalendar);
document.addEventListener("turbo:before-cache", cleanupCalendar);
document.addEventListener("turbo:before-visit", cleanupCalendar);

// Export for manual initialization if needed
window.initializeCalendar = initializeCalendar;
