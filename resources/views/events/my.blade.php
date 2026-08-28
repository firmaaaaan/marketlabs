@extends('layouts.account')

@section('title', 'Riwayat Event - MarketLabs')

@section('account-content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Riwayat Event</h1>
        <p class="mt-1 text-sm text-slate-600">Pantau registrasi, isi presensi, dan unduh sertifikat event Anda.</p>
    </div>
    <a href="{{ route('events.index') }}"
       class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
        Lihat Event Tersedia
    </a>
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if ($registrations->isEmpty())
    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center">
        <p class="text-xl font-bold text-slate-700">Belum ada registrasi event</p>
        <p class="mt-2 text-sm text-slate-500">Temukan event menarik dan daftarkan diri Anda.</p>
    </div>
@else
    <div class="mt-6 space-y-5">
        @foreach ($registrations as $registration)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold text-slate-400">{{ $registration->event->code }}</p>
                        <h2 class="mt-0.5 text-lg font-extrabold text-slate-900">{{ $registration->event->title }}</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $registration->event->starts_at?->translatedFormat('l, d F Y · H:i') ?? 'Belum dijadwalkan' }}
                            · {{ $registration->event->location ?? '-' }}
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($registration->status) {
                                \App\Models\EventRegistration::STATUS_PENDING => 'bg-amber-50 text-amber-700',
                                \App\Models\EventRegistration::STATUS_REGISTERED => 'bg-emerald-50 text-emerald-700',
                                \App\Models\EventRegistration::STATUS_CANCELLED => 'bg-slate-100 text-slate-500',
                                default => 'bg-slate-100 text-slate-500',
                            } }}">
                                {{ $registration->status_label }}
                            </span>
                            @if ($registration->event->attendance_enabled)
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $registration->is_attended ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $registration->is_attended ? 'Presensi terisi' : 'Belum presensi' }}
                            </span>
                            @endif
                            @if ($registration->has_certificate)
                                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                                    {{ $registration->certificate_number }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($registration->event->attendance_enabled && $registration->status === \App\Models\EventRegistration::STATUS_REGISTERED && ! $registration->is_attended)
                            <a href="{{ route('events.attendance', $registration->attendance_token) }}"
                               class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                Isi Presensi
                            </a>
                        @endif
                        @if ($registration->has_certificate)
                            <a href="{{ route('events.certificate', $registration) }}" target="_blank"
                               class="rounded-lg border border-sky-200 bg-white px-4 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50">
                                Lihat Sertifikat
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($registrations->hasPages())
        <div class="mt-8">
            {{ $registrations->links() }}
        </div>
    @endif
@endif

@if ($proxied->isNotEmpty())
    <div class="mt-10">
        <h2 class="text-xl font-extrabold text-slate-900">Yang Saya Daftarkan</h2>
        <p class="mt-1 text-sm text-slate-600">Teman yang berhasil Anda daftarkan atas nama mereka. Presensi dan sertifikat dikelola masing-masing di akun mereka.</p>

        <div class="mt-5 space-y-5">
            @foreach ($proxied as $registration)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-400">{{ $registration->event->code }}</p>
                            <h3 class="mt-0.5 text-lg font-extrabold text-slate-900">{{ $registration->user->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $registration->event->title }}
                                · {{ $registration->event->starts_at?->translatedFormat('d M Y') ?? 'Belum dijadwalkan' }}
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($registration->status) {
                                    \App\Models\EventRegistration::STATUS_PENDING => 'bg-amber-50 text-amber-700',
                                    \App\Models\EventRegistration::STATUS_REGISTERED => 'bg-emerald-50 text-emerald-700',
                                    \App\Models\EventRegistration::STATUS_CANCELLED => 'bg-slate-100 text-slate-500',
                                    default => 'bg-slate-100 text-slate-500',
                                } }}">
                                    {{ $registration->status_label }}
                                </span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $registration->is_attended ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $registration->is_attended ? 'Presensi terisi' : 'Belum presensi' }}
                                </span>
                                @if ($registration->has_certificate)
                                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                                        {{ $registration->certificate_number }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($proxied->hasPages())
            <div class="mt-8">
                {{ $proxied->links() }}
            </div>
        @endif
    </div>
@endif

@endsection