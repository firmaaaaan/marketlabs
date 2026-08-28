@extends('layouts.app')

@section('title', 'Katalog Pemeriksaan Kesehatan - MarketLabs')

@section('content')

{{-- Header --}}
<section class="bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-900 pt-32 pb-20">
    <div class="pointer-events-none absolute"></div>
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold text-emerald-100 backdrop-blur">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            Pemeriksaan Kesehatan
        </span>
        <h1 class="mt-4 max-w-2xl text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
            Booking Pemeriksaan Kesehatan
        </h1>
        <p class="mt-4 max-w-2xl text-lg leading-relaxed text-emerald-50/85">
            Pilih jenis pemeriksaan, tentukan tanggal kedatangan, dan dapatkan nomor antrian harian.
            Hasil pemeriksaan dapat diunduh sebagai surat keterangan resmi.
        </p>

        @if ($schedule['enabled'])
            <div class="mt-6 flex max-w-2xl flex-wrap items-center gap-x-6 gap-y-2 rounded-2xl border border-white/15 bg-white/10 px-6 py-4 text-sm text-emerald-50 backdrop-blur">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <span><span class="font-semibold text-white">Hari operasional:</span> {{ $schedule['day_names'] }}</span>
                </span>
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><span class="font-semibold text-white">Jam layanan:</span> {{ $schedule['open_time'] }}–{{ $schedule['close_time'] }}</span>
                </span>
                @if ($schedule['break_start'] && $schedule['break_end'])
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                        </svg>
                        <span><span class="font-semibold text-white">Istirahat:</span> {{ $schedule['break_start'] }}–{{ $schedule['break_end'] }}</span>
                    </span>
                @endif
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span><span class="font-semibold text-white">Kuota:</span> {{ $schedule['quota'] }} booking/hari</span>
                </span>
            </div>
        @endif
    </div>
</section>

{{-- Daftar jenis pemeriksaan --}}
<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2">
            @forelse ($types as $type)
                <div class="group relative flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-xl">
                    <div class="absolute -top-12 -right-12 h-36 w-36 rounded-full bg-emerald-50 transition duration-300 group-hover:scale-150"></div>

                    <div class="relative">
                        <div class="flex items-start justify-between gap-4">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-600/25">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </span>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Tersedia</span>
                        </div>

                        <h2 class="mt-5 text-2xl font-extrabold text-slate-900">{{ $type->name }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $type->description }}</p>

                        <div class="mt-6 flex items-end justify-between gap-4 border-t border-slate-100 pt-5">
                            <div>
                                <p class="text-2xl font-extrabold text-emerald-700">{{ $type->formatted_price }}</p>
                                <p class="text-xs text-slate-400">per pemeriksaan</p>
                            </div>
                            <a href="{{ route('health-checkups.create', ['type' => $type->key]) }}"
                               class="rounded-xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700">
                                Booking Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <p class="text-lg font-semibold text-slate-700">Belum ada jenis pemeriksaan tersedia</p>
                    <p class="mt-1 text-sm text-slate-500">Jenis pemeriksaan akan muncul di sini setelah admin menambahkannya.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@endsection
