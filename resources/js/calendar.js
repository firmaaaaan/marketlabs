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

            const myOnlyEl = document.getElementById('filter-my-only');
            if (myOnlyEl && myOnlyEl.checked) {
                url.searchParams.set('my_only', '1');
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
            const type = props.type;

            const config = {
                borrowing: { color: 'from-blue-600 to-blue-700', label: 'Peminjaman', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4' },
                health_checkup: { color: 'from-emerald-600 to-emerald-700', label: 'Pemeriksaan Kesehatan', icon: 'M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3.75V7.5m0 0v3.75m-3-3.75h6' },
                research: { color: 'from-violet-600 to-violet-700', label: 'Riset & Penelitian', icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z' },
                event: { color: 'from-orange-500 to-orange-600', label: 'Event', icon: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5' },
            };

            const c = config[type] || config.event;

            // Header
            document.getElementById('modal-header').className = `flex items-center gap-3 bg-gradient-to-r ${c.color} px-6 py-4`;
            document.getElementById('modal-type').textContent = c.label;
            document.getElementById('modal-title').textContent = info.event.title;
            document.getElementById('modal-icon').innerHTML = `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="${c.icon}" /></svg>`;

            // Status badge colors
            const statusColors = {
                approved: 'bg-blue-50 text-blue-700 ring-blue-600/20',
                borrowed: 'bg-amber-50 text-amber-700 ring-amber-600/20',
                pending: 'bg-amber-50 text-amber-700 ring-amber-600/20',
                ongoing: 'bg-violet-50 text-violet-700 ring-violet-600/20',
                active: 'bg-orange-50 text-orange-700 ring-orange-600/20',
            };
            const badgeClass = statusColors[props.status] || 'bg-slate-50 text-slate-700 ring-slate-600/20';

            // Build content rows
            let rows = '';
            rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                <span class="text-xs font-medium text-slate-500">Status</span>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${badgeClass}">${props.status_label}</span>
            </div>`;

            if (props.user) {
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Pengguna</span>
                    <span class="text-sm font-semibold text-slate-800">${props.user}</span>
                </div>`;
            }

            if (type === 'borrowing') {
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Kode</span>
                    <span class="text-sm font-semibold text-slate-800">${props.code}</span>
                </div>`;
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Tanggal</span>
                    <span class="text-sm font-semibold text-slate-800">${props.borrow_date} s/d ${props.return_date}</span>
                </div>`;
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Lama</span>
                    <span class="text-sm font-semibold text-slate-800">${props.duration_days} hari</span>
                </div>`;
            } else if (type === 'health_checkup') {
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Kode</span>
                    <span class="text-sm font-semibold text-slate-800">${props.code}</span>
                </div>`;
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">No. Antrian</span>
                    <span class="text-sm font-semibold text-slate-800">${props.queue_number}</span>
                </div>`;
                if (props.type_name) {
                    rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                        <span class="text-xs font-medium text-slate-500">Jenis</span>
                        <span class="text-sm font-semibold text-slate-800">${props.type_name}</span>
                    </div>`;
                }
            } else if (type === 'research') {
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Kode</span>
                    <span class="text-sm font-semibold text-slate-800">${props.code}</span>
                </div>`;
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Judul</span>
                    <span class="text-right text-sm font-semibold text-slate-800">${props.title}</span>
                </div>`;
                if (props.lab) {
                    rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                        <span class="text-xs font-medium text-slate-500">Laboratorium</span>
                        <span class="text-sm font-semibold text-slate-800">${props.lab}</span>
                    </div>`;
                }
                if (props.laboran) {
                    rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                        <span class="text-xs font-medium text-slate-500">Laboran</span>
                        <span class="text-sm font-semibold text-slate-800">${props.laboran}</span>
                    </div>`;
                }
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Periode</span>
                    <span class="text-sm font-semibold text-slate-800">${props.start_date} s/d ${props.end_date} (${props.duration_days} hari)</span>
                </div>`;
            } else if (type === 'event') {
                rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                    <span class="text-xs font-medium text-slate-500">Kode</span>
                    <span class="text-sm font-semibold text-slate-800">${props.code}</span>
                </div>`;
                if (props.mode) {
                    rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                        <span class="text-xs font-medium text-slate-500">Mode</span>
                        <span class="text-sm font-semibold text-slate-800">${props.mode}</span>
                    </div>`;
                }
                if (props.location) {
                    rows += `<div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-2.5">
                        <span class="text-xs font-medium text-slate-500">Lokasi</span>
                        <span class="text-sm font-semibold text-slate-800">${props.location}</span>
                    </div>`;
                }
            }

            document.getElementById('modal-content').innerHTML = `<div class="space-y-2">${rows}</div>`;
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

    const myOnlyEl = document.getElementById('filter-my-only');
    if (myOnlyEl) {
        myOnlyEl.addEventListener('change', () => calendar.refetchEvents());
    }

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
