define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function() {
            const today = new Date();
            const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
            const endOfWeek = new Date(today.setDate(startOfWeek.getDate() + 6));

            const timestart = Math.floor(startOfWeek.setHours(0,0,0,0) / 1000);
            const timeend = Math.floor(endOfWeek.setHours(23,59,59,999) / 1000);

            Ajax.call([{
                methodname: 'core_calendar_get_action_events_by_timesort',
                args: {
                    timesortfrom: timestart,
                    timesortto: timeend,
                    limitnum: 100
                },
                done: function(response) {
                    response.events.forEach(event => {
                        const eventDate = new Date(event.timestart * 1000);
                        const dayName = eventDate.toLocaleDateString('en-US', { weekday: 'long' });

                        const selector = `.day-column h4:contains("${dayName}")`;
                        const container = $(selector).closest('.day-column').find('.day-content');

                        container.html(''); // Clear "No events yet"
                        container.append(`<div class="event">${event.name}</div>`);
                    });
                },
                fail: function(err) {
                    console.error("Failed to load events:", err);
                }
            }]);
        }
    };
});

