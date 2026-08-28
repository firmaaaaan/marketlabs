@extends('layouts.app')

@section('title', 'Event - MarketLabs')

@section('content')

{{-- Header --}}
<section class="bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-900 pt-32 pb-20">
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold text-emerald-100 backdrop-blur">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            Event &amp; Kegiatan
        </span>
        <h1 class="mt-4 max-w-2xl text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
            Event Laboratorium
        </h1>
        <p class="mt-4 max-w-2xl text-lg leading-relaxed text-emerald-50/85">
            Daftarkan diri Anda pada workshop, seminar, dan pelatihan yang kami selenggarakan.
            Setiap peserta yang hadir akan mendapatkan sertifikat digital resmi.
        </p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    @if ($events->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-16 text-center">
            <p class="text-xl font-bold text-slate-700">Belum ada event</p>
            <p class="mt-2 text-sm text-slate-500">Pantau terus halaman ini untuk event mendatang.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)
                <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    @if ($event->image || $event->poster)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($event->image ?: $event->poster) }}" alt="{{ $event->title }}"
                             class="h-44 w-full object-cover">
                    @else
                        <div class="flex h-44 w-full items-center justify-center bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-900">
                            <svg class="h-12 w-12 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                    @endif
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex items-center justify-between gap-3">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $event->status === \App\Models\Event::STATUS_ACTIVE ? 'bg-emerald-50 text-emerald-700' : 'bg-sky-50 text-sky-700' }}">
                                {{ $event->status_label }}
                            </span>
                            @if ($event->mode)
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($event->mode) {
                                    \App\Models\Event::MODE_ONLINE => 'bg-sky-50 text-sky-700',
                                    \App\Models\Event::MODE_OFFLINE => 'bg-amber-50 text-amber-700',
                                    \App\Models\Event::MODE_HYBRID => 'bg-violet-50 text-violet-700',
                                    default => 'bg-slate-100 text-slate-500',
                                } }}">
                                    {{ $event->mode_label }}
                                </span>
                            @endif
                        </div>
                        @if ($event->is_open)
                            <span class="mt-2 inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                Buka Pendaftaran
                            </span>
                        @endif
                        <h2 class="mt-3 text-lg font-extrabold text-slate-900">{{ $event->title }}</h2>
                        <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-slate-600">
                            {{ Str::limit(strip_tags($event->description ?? ''), 160) }}
                        </p>
                        <dl class="mt-5 space-y-2 text-sm text-slate-600">
                            @if ($event->starts_at)
                                <div class="flex items-center gap-2.5">
                                    <svg class="h-4 w-4 flex-none text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    <span>{{ $event->starts_at->translatedFormat('l, d F Y · H:i') }}</span>
                                </div>
                            @endif
                            @if ($event->location)
                                <div class="flex items-center gap-2.5">
                                    <svg class="h-4 w-4 flex-none text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    <span>{{ $event->location }}</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-2.5">
                                <svg class="h-4 w-4 flex-none text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                <span>
                                    {{ $event->registrations_count }} peserta
                                    @if ($event->quota)
                                        dari {{ $event->quota }} kuota
                                    @endif
                                </span>
                            </div>
                        </dl>
                        <div class="mt-4 flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                            @if ($event->has_fee)
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Biaya Pendaftaran</p>
                                    @if ($event->has_discount)
                                        <p class="text-xs text-slate-400 line-through">{{ $event->fee_label }}</p>
                                        <p class="text-sm font-extrabold text-slate-900">{{ $event->effective_fee_label }}</p>
                                    @else
                                        <p class="text-sm font-extrabold text-slate-900">{{ $event->fee_label }}</p>
                                    @endif
                                </div>
                                @if ($event->has_discount)
                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600">
                                        -{{ $event->discount_percent ?? $event->discount_label }}
                                    </span>
                                @endif
                            @else
                                <p class="text-sm font-bold text-emerald-600">Gratis</p>
                            @endif
                        </div>
                        <a href="{{ route('events.show', $event) }}"
                           class="mt-6 rounded-lg bg-emerald-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($events->hasPages())
            <div class="mt-10">
                {{ $events->links() }}
            </div>
        @endif
    @endif
</section>

@endsection