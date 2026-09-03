@extends('layouts.app')

@section('title', 'Jadwal Lab - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Jadwal Laboratorium</h1>
            <p class="mt-3 text-slate-600">Informasi jam operasional dan event mendatang dari UPT Laboratorium Terpadu UNISA Yogyakarta.</p>
        </div>

        {{-- Jam Operasional --}}
        <div class="mt-12 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Jam Operasional</h2>
                    @if ($scheduleEnabled)
                        <p class="text-sm text-slate-500">{{ $openTime }} – {{ $closeTime }} WIB</p>
                    @else
                        <p class="text-sm text-slate-500">Jadwal belum diaktifkan oleh admin</p>
                    @endif
                </div>
            </div>

            @if ($scheduleEnabled)
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-7">
                    @foreach ($schedule as $day)
                        <div class="rounded-xl border p-3 text-center transition {{ $day['is_open'] ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                            <p class="text-xs font-bold {{ $day['is_open'] ? 'text-emerald-700' : 'text-slate-400' }}">{{ substr($day['name'], 0, 3) }}</p>
                            @if ($day['is_open'])
                                <p class="mt-1 text-[10px] font-semibold text-emerald-600">{{ $openTime }}–{{ $closeTime }}</p>
                                <span class="mt-1.5 inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            @else
                                <p class="mt-1 text-[10px] font-semibold text-slate-400">Tutup</p>
                                <span class="mt-1.5 inline-block h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                    <p class="text-sm font-medium text-amber-700">Jadwal operasional belum dikonfigurasi.</p>
                </div>
            @endif
        </div>

        {{-- Event Mendatang --}}
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100">
                    <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Event Mendatang</h2>
                    <p class="text-sm text-slate-500">Workshop, seminar, dan pelatihan dari laboratorium.</p>
                </div>
            </div>

            @if ($upcomingEvents->isEmpty())
                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-center">
                    <p class="text-sm font-medium text-slate-500">Belum ada event mendatang.</p>
                </div>
            @else
                <div class="mt-6 space-y-3">
                    @foreach ($upcomingEvents as $event)
                        <a href="{{ $event['url'] }}" target="_blank"
                           class="block rounded-xl border border-slate-200 p-4 transition hover:border-emerald-200 hover:bg-emerald-50/30">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ $event['title'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $event['starts_at']->translatedFormat('l, d M Y') }} · {{ $event['starts_at']->format('H:i') }}–{{ $event['ends_at']->format('H:i') }} WIB
                                    </p>
                                    @if ($event['location'])
                                        <p class="mt-0.5 text-sm text-slate-500">{{ $event['location'] }}</p>
                                    @endif
                                </div>
                                <span class="flex-none rounded-full px-3 py-1 text-xs font-semibold {{ match($event['mode']) {
                                    'Online' => 'bg-sky-50 text-sky-700',
                                    'Offline' => 'bg-emerald-50 text-emerald-700',
                                    'Hybrid' => 'bg-violet-50 text-violet-700',
                                    default => 'bg-slate-100 text-slate-600',
                                } }}">
                                    {{ $event['mode'] }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 text-center">
                <a href="{{ route('events.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
                    Lihat Semua Event
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- CTA --}}
        <div class="mt-8 text-center">
            <p class="text-sm text-slate-500">Butuh layanan laboratorium?</p>
            <div class="mt-3 flex flex-wrap justify-center gap-3">
                <a href="{{ route('tools.index') }}"
                   class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                    Pinjam Alat
                </a>
                <a href="{{ route('health-checkups.catalog') }}"
                   class="rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                    Pemeriksaan Kesehatan
                </a>
            </div>
        </div>

    </div>
</section>

@endsection
