@extends('layouts.app')

@section('title', 'Presensi - '.$registration->event->title.' - MarketLabs')

@section('content')

<section class="bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-900 pt-32 pb-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold text-emerald-100 backdrop-blur">
            Presensi Peserta
        </span>
        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ $registration->event->title }}</h1>
        <p class="mt-3 text-emerald-50/85">{{ $registration->user->name }} · {{ $registration->user->email }}</p>
    </div>
</section>

<section class="mx-auto max-w-2xl px-4 py-14 sm:px-6 lg:px-8">
    @if ($registration->is_attended)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-8 text-center shadow-sm">
            <p class="text-4xl">🎉</p>
            <h2 class="mt-3 text-xl font-extrabold text-emerald-800">Presensi Anda sudah tercatat</h2>
            <p class="mt-1 text-sm text-slate-600">
                Terima kasih sudah hadir pada {{ $registration->attended_at->translatedFormat('l, d F Y · H:i') }}.
            </p>
            @if ($registration->has_certificate)
                <a href="{{ route('events.certificate', $registration) }}" target="_blank"
                   class="mt-5 inline-block rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Lihat Sertifikat
                </a>
            @endif
        </div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-extrabold text-slate-900">Form Presensi</h2>
            <p class="mt-1 text-sm text-slate-500">Isi form kehadiran di bawah ini untuk mencatat kehadiran Anda.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <p class="text-sm font-bold text-red-700">Periksa kembali isian Anda:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('events.attendance.store', $registration->attendance_token) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                @include('events._fields', ['fields' => \App\Support\FormFields::normalize($registration->event->attendance_fields)])

                <button type="submit"
                        class="mt-2 w-full rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                    Konfirmasi Kehadiran
                </button>
            </form>
        </div>
    @endif
</section>

@endsection