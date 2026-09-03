@extends('layouts.admin')

@section('title', 'Kelola Event - MarketLabs')

@section('page', 'Kelola Event')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Daftar Event</h1>
        <p class="mt-1 text-sm text-slate-600">Kelola event, form registrasi &amp; presensi, serta sertifikat peserta.</p>
    </div>
    <a href="{{ route('admin.events.create') }}"
       class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
        + Buat Event
    </a>
</div>

{{-- Filter status --}}
<div class="mt-6 flex flex-wrap gap-2">
    @php
        $statuses = [
            '' => 'Semua',
            \App\Models\Event::STATUS_DRAFT => 'Draf',
            \App\Models\Event::STATUS_ACTIVE => 'Aktif',
            \App\Models\Event::STATUS_CLOSED => 'Ditutup',
            \App\Models\Event::STATUS_COMPLETED => 'Selesai',
        ];
    @endphp
    @foreach ($statuses as $value => $label)
        <a href="{{ route('admin.events.index', ['status' => $value]) }}"
           class="rounded-full px-4 py-1.5 text-xs font-semibold transition {{ request('status') === $value ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-emerald-50' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Event</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jadwal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Peserta</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sertifikat</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($events as $event)
                    <tr class="transition hover:bg-slate-50/60">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if ($event->image || $event->poster)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($event->image ?: $event->poster) }}" alt="{{ $event->title }}"
                                         class="h-12 w-16 flex-none rounded-lg object-cover">
                                @endif
                                <div>
                                    <a href="{{ route('admin.events.show', $event) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                        {{ $event->title }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $event->code }} · {{ $event->location ?? 'Tanpa lokasi' }}</p>
                                    @if ($event->mode)
                                        <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[11px] font-semibold {{ match ($event->mode) {
                                            \App\Models\Event::MODE_ONLINE => 'bg-sky-50 text-sky-700',
                                            \App\Models\Event::MODE_OFFLINE => 'bg-amber-50 text-amber-700',
                                            \App\Models\Event::MODE_HYBRID => 'bg-violet-50 text-violet-700',
                                            default => 'bg-slate-100 text-slate-500',
                                        } }}">
                                            {{ $event->mode_label }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($event->status) {
                                \App\Models\Event::STATUS_ACTIVE => 'bg-emerald-50 text-emerald-700',
                                \App\Models\Event::STATUS_DRAFT => 'bg-slate-100 text-slate-500',
                                \App\Models\Event::STATUS_CLOSED => 'bg-amber-50 text-amber-700',
                                \App\Models\Event::STATUS_COMPLETED => 'bg-sky-50 text-sky-700',
                                default => 'bg-slate-100 text-slate-500',
                            } }}">
                                {{ $event->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-slate-600">
                            @if ($event->starts_at)
                                {{ $event->starts_at->translatedFormat('d M Y') }}
                                @if ($event->ends_at)
                                    <span class="text-slate-400">– {{ $event->ends_at->translatedFormat('d M Y') }}</span>
                                @endif
                            @else
                                <span class="text-slate-400">Belum dijadwalkan</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm font-semibold text-slate-700">
                            {{ $event->registrations_count }}
                            @if ($event->quota)
                                <span class="font-normal text-slate-400">/ {{ $event->quota }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm {{ $event->certificate_ready ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $event->certificate_ready ? 'Siap' : 'Belum diatur' }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.events.show', $event) }}"
                                   class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600">
                                    Detail
                                </a>
                                <a href="{{ route('admin.events.edit', $event) }}"
                                   class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600"
                                   title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-14 text-center">
                            <p class="text-lg font-semibold text-slate-700">Belum ada event</p>
                            <p class="mt-1 text-sm text-slate-500">Buat event pertama Anda untuk mulai menerima pendaftaran.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($events->hasPages())
    <div class="mt-6">
        {{ $events->links() }}
    </div>
@endif

@endsection