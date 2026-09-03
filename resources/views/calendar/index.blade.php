@extends('layouts.account')

@section('title', 'Kalender - MarketLabs')

@section('account-content')

<div>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kalender</h1>
            <p class="mt-1 text-sm text-slate-600">Lihat jadwal peminjaman, pemeriksaan kesehatan, riset, dan event.</p>
        </div>
        <a href="{{ route('calendar.export') }}"
           class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export .ics
        </a>
    </div>

    {{-- Legend --}}
    <div class="mt-6 flex flex-wrap gap-4">
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded-full border-2 border-blue-500 bg-blue-100"></span>
            <span class="text-sm text-slate-600">Peminjaman (Disetujui)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded-full border-2 border-amber-500 bg-amber-100"></span>
            <span class="text-sm text-slate-600">Peminjaman (Dipinjam)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded-full border-2 border-amber-500 bg-amber-100"></span>
            <span class="text-sm text-slate-600">MCU (Menunggu)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded-full border-2 border-emerald-500 bg-emerald-100"></span>
            <span class="text-sm text-slate-600">MCU (Terjadwal)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded-full border-2 border-violet-500 bg-violet-100"></span>
            <span class="text-sm text-slate-600">Riset</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded-full border-2 border-orange-500 bg-orange-100"></span>
            <span class="text-sm text-slate-600">Event</span>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mt-4 flex flex-wrap gap-3">
        <label class="flex items-center gap-2">
            <input type="checkbox" id="filter-borrowing" data-type="borrowing" checked class="calendar-filter rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-slate-700">Peminjaman</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" id="filter-health-checkup" data-type="health_checkup" checked class="calendar-filter rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-slate-700">Pemeriksaan Kesehatan</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" id="filter-research" data-type="research" checked class="calendar-filter rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-slate-700">Riset</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" id="filter-event" data-type="event" checked class="calendar-filter rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-slate-700">Event</span>
        </label>
        <label class="flex items-center gap-2 ml-4 pl-4 border-l border-slate-200">
            <input type="checkbox" id="filter-my-only" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-slate-700">Hanya milik saya</span>
        </label>
    </div>

    {{-- Calendar --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div id="calendar" data-events-url="{{ route('calendar.events') }}"></div>
    </div>

    {{-- Event Detail Modal --}}
    <div id="event-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div id="modal-header" class="flex items-center gap-3 px-6 py-4">
                <span id="modal-icon" class="flex h-10 w-10 items-center justify-center rounded-xl text-white"></span>
                <div class="min-w-0 flex-1">
                    <p id="modal-type" class="text-xs font-semibold uppercase tracking-wider text-white/80"></p>
                    <h3 id="modal-title" class="truncate text-base font-bold text-white"></h3>
                </div>
                <button onclick="closeModal()" class="flex h-8 w-8 items-center justify-center rounded-lg text-white/70 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="modal-content" class="px-6 py-4"></div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-3">
                <button onclick="closeModal()" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Tutup
                </button>
                <a id="modal-link" href="#" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                    Lihat Detail
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .fc {
        font-family: inherit;
    }
    .fc .fc-toolbar-title {
        font-size: 1.125rem;
        font-weight: 700;
    }
    .fc .fc-button {
        background-color: #fff;
        border-color: #e2e8f0;
        color: #475569;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
    }
    .fc .fc-button:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }
    .fc .fc-button-active {
        background-color: #059669 !important;
        border-color: #059669 !important;
        color: #fff !important;
    }
    .fc .fc-daygrid-day-number {
        font-size: 0.875rem;
        padding: 0.5rem;
    }
    .fc .fc-event {
        border-radius: 0.375rem;
        padding: 2px 6px;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        border-width: 2px;
    }
    .fc .fc-col-header-cell {
        padding: 0.75rem;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .fc .fc-scrollgrid {
        border-color: #e2e8f0;
    }
    .fc th {
        border-color: #e2e8f0;
    }
    .fc td, .fc th {
        border-color: #e2e8f0;
    }
</style>

@vite(['resources/js/calendar.js'])

@endsection
