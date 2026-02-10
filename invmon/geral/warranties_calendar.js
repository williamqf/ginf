// function showCalendar(div, worker_id = '') {
function showCalendar(div, args) {
    // document.addEventListener("DOMContentLoaded", function() {
        
        // const backgroundColorOpened = $('#opened-colors').css('background-color');
        // const colorOpened = $('#opened-colors').css('color');
        // const borderColorOpened = $('#opened-colors').css('border-color');

        // const backgroundColorClosed = $('#closed-colors').css('background-color');
        // const colorClosed = $('#closed-colors').css('color');
        // const borderColorClosed = $('#closed-colors').css('border-color');

        // const backgroundColorScheduled = $('#scheduled-colors').css('background-color');
        // const colorScheduled = $('#scheduled-colors').css('color');
        // const borderColorScheduled = $('#scheduled-colors').css('border-color');


        let defaults = {
            client: ''
        }
        let params = {...defaults, ...args};

        // console.log('worker_id: ' + params.worker_id);
        // console.log('client: ' + params.client);
        // console.log('area: ' + params.area);
        // console.log('opened: ' + params.opened);
        // console.log('closed: ' + params.closed);
        // console.log('scheduled: ' + params.scheduled);
       

        let eventSources = [];
        
        eventSources.push ({
            url: './get_calendar_events_warranties_expiring_dates.php',
                method: 'POST',
                extraParams: {
                    // color: colorScheduled,
                    // bgColor: backgroundColorScheduled,
                    // borderColor: borderColorScheduled,
                    // worker_id: params.worker_id,
                    // area: params.area,
                    client: params.client,
                    function() {
                        return {
                            cachebuster: new Date().valueOf()
                        };
                    },
                },
                failure: function() {
                    alert('there was an error while fetching events from: get_calendar_events_warranties_expiring_dates');
                },
        });
        
    
        let calendarEl = document.getElementById(div);
        let calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            themeSystem: 'bootstrap',
            // handleWindowResize: true,
            initialView: "dayGridMonth",
            eventDisplay: 'block',
            selectable: true,
            editable: true,
            dayMaxEventRows: 4, // for all non-TimeGrid views
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            height: 600,
            eventSources: eventSources,
            eventTimeFormat: { // like '14:30:00'
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            eventClick: function(info) {

                info.jsEvent.preventDefault(); // don't let the browser navigate

                $('#eventAssetId').val(info.event.id);
                $('#asset_type').text(info.event.extendedProps.asset_type);
                $('#calAssetId').text(info.event.id);
                $('#client').text(info.event.extendedProps.client);
                $('#unit').text(info.event.extendedProps.unit);
                $('#tag').text(info.event.extendedProps.tag);
                $('#purchase_date').text(info.event.extendedProps.purchase_date);
                $('#warranty_expire').text(info.event.extendedProps.expiring_date);
                $('#department').text(info.event.extendedProps.departamento);
                $('#comment').html(info.event.extendedProps.comment);

                $('#modalEvent').modal('show');

                // if (info.event.url) {
                //     window.open(info.event.url);
                // }
                
                // // change the border color just for fun
                // info.el.style.borderColor = 'red';
            },
            dateClick: function(info) {
                if (info.view.type == 'dayGridMonth') {
                    calendar.changeView('timeGridDay', info.dateStr);
                }
            },
            windowResize: function(arg) {
                calendar.updateSize();
            }

        });
        calendar.setOption('locale', 'pt-br');

        // calendar.refetchEvents();
        calendar.render();
        // calendar.next();

        return calendar;
        
        // calendar.updateSize();
    // });
}