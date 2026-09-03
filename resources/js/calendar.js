import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

function initCalendar(calendarEl, options = {}) {
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari',
        },
        locale: 'id',
        height: 'auto',
        events: function (info, successCallback, failureCallback) {
            const url = new URL(options.eventsUrl, window.location.origin);
            url.searchParams.set('start', info.startStr);
            url.searchParams.set('end', info.endStr);

            if (options.myOnly) {
                const myOnlyEl = document.getElementById('filter-my-only');
                if (myOnlyEl && myOnlyEl.checked) {
                    url.searchParams.set('my_only', '1');
                }
            }

            fetch(url)
                .then(response => response.json())
                .then(events => {
                    const filters = {};
                    document.querySelectorAll('.calendar-filter').forEach(el => {
                        filters[el.dataset.type] = el.checked;
                    });

                    const hasFilters = Object.keys(filters).length > 0;
                    const filtered = hasFilters
                        ? events.filter(e => filters[e.extendedProps.type] !== false)
                        : events;
                    successCallback(filtered);
                })
                .catch(error => failureCallback(error));
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            const props = info.event.extendedProps;
            document.getElementById('modal-title').textContent = info.event.title;

            let content = '';
            content += `<div><span class="font-semibold text-slate-700">Status:</span> ${props.status_label}</div>`;
            if (props.user) content += `<div><span class="font-semibold text-slate-700">User:</span> ${props.user}</div>`;

            if (props.type === 'borrowing') {
                content += `<div><span class="font-semibold text-slate-700">Kode:</span> ${props.code}</div>`;
                content += `<div><span class="font-semibold text-slate-700">Tanggal Pinjam:</span> ${props.borrow_date}</div>`;
                content += `<div><span class="font-semibold text-slate-700">Tanggal Kembali:</span> ${props.return_date}</div>`;
                content += `<div><span class="font-semibold text-slate-700">Lama:</span> ${props.duration_days} hari</div>`;
            } else if (props.type === 'health_checkup') {
                content += `<div><span class="font-semibold text-slate-700">Kode:</span> ${props.code}</div>`;
                content += `<div><span class="font-semibold text-slate-700">No. Antrian:</span> ${props.queue_number}</div>`;
                if (props.type_name) content += `<div><span class="font-semibold text-slate-700">Jenis:</span> ${props.type_name}</div>`;
            } else if (props.type === 'research') {
                content += `<div><span class="font-semibold text-slate-700">Kode:</span> ${props.code}</div>`;
                content += `<div><span class="font-semibold text-slate-700">Judul:</span> ${props.title}</div>`;
                if (props.lab) content += `<div><span class="font-semibold text-slate-700">Lab:</span> ${props.lab}</div>`;
                if (props.laboran) content += `<div><span class="font-semibold text-slate-700">Laboran:</span> ${props.laboran}</div>`;
                content += `<div><span class="font-semibold text-slate-700">Periode:</span> ${props.start_date} s/d ${props.end_date} (${props.duration_days} hari)</div>`;
            } else if (props.type === 'event') {
                content += `<div><span class="font-semibold text-slate-700">Kode:</span> ${props.code}</div>`;
                if (props.mode) content += `<div><span class="font-semibold text-slate-700">Mode:</span> ${props.mode}</div>`;
                if (props.location) content += `<div><span class="font-semibold text-slate-700">Lokasi:</span> ${props.location}</div>`;
            }

            document.getElementById('modal-content').innerHTML = content;
            document.getElementById('modal-link').href = props.url;

            const modal = document.getElementById('event-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        },
    });

    calendar.render();

    document.querySelectorAll('.calendar-filter').forEach(el => {
        el.addEventListener('change', () => calendar.refetchEvents());
    });

    return calendar;
}

function closeModal() {
    const modal = document.getElementById('event-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    initCalendar(calendarEl, {
        eventsUrl: calendarEl.dataset.eventsUrl,
        myOnly: calendarEl.dataset.myOnly === '1',
    });

    const modal = document.getElementById('event-modal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

    window.closeModal = closeModal;
});
