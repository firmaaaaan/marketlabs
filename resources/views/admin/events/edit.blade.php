@extends('layouts.admin')

@section('title', 'Edit Event - MarketLabs')

@section('page', 'Edit Event')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Edit Event</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $event->code }} · {{ $event->title }}</p>
    </div>
    <a href="{{ route('admin.events.show', $event) }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        ← Kembali ke Detail
    </a>
</div>

@if ($errors->any())
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-sm font-bold text-red-700">Periksa kembali isian Anda:</p>
        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $initialForm = json_decode(old('form_fields', json_encode($event->form_fields ?? [])), true) ?: [];
    $initialAttendance = json_decode(old('attendance_fields', json_encode($event->attendance_fields ?? [])), true) ?: [];
    $oldInput = fn ($key) => old($key, $event->{$key});
@endphp

<form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="mt-8 space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
        <h2 class="text-base font-bold text-slate-900">Detail Event</h2>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label for="title" class="block text-sm font-semibold text-slate-600">Judul Event <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ $oldInput('title') }}" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="location" class="block text-sm font-semibold text-slate-600">Lokasi</label>
                <input type="text" id="location" name="location" value="{{ $oldInput('location') }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="starts_at" class="block text-sm font-semibold text-slate-600">Mulai</label>
                <input type="datetime-local" id="starts_at" name="starts_at"
                       value="{{ $event->starts_at ? $event->starts_at->format('Y-m-d\TH:i') : '' }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="ends_at" class="block text-sm font-semibold text-slate-600">Selesai</label>
                <input type="datetime-local" id="ends_at" name="ends_at"
                       value="{{ $event->ends_at ? $event->ends_at->format('Y-m-d\TH:i') : '' }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="quota" class="block text-sm font-semibold text-slate-600">Kuota Peserta</label>
                <input type="number" id="quota" name="quota" value="{{ $oldInput('quota') }}" min="1"
                       placeholder="Kosongkan = tanpa batas"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="registration_deadline" class="block text-sm font-semibold text-slate-600">Tenggat Pendaftaran</label>
                <input type="datetime-local" id="registration_deadline" name="registration_deadline"
                       value="{{ $event->registration_deadline ? $event->registration_deadline->format('Y-m-d\TH:i') : '' }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            @include('admin.events._pricing', ['event' => $event])
            <div>
                <label for="status" class="block text-sm font-semibold text-slate-600">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    @foreach ([
                        \App\Models\Event::STATUS_DRAFT => 'Draf',
                        \App\Models\Event::STATUS_ACTIVE => 'Aktif',
                        \App\Models\Event::STATUS_CLOSED => 'Ditutup',
                        \App\Models\Event::STATUS_COMPLETED => 'Selesai',
                    ] as $value => $label)
                        <option value="{{ $value }}" {{ $oldInput('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mode" class="block text-sm font-semibold text-slate-600">Kategori Event</label>
                <select id="mode" name="mode"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">-- Pilih --</option>
                    @foreach (\App\Models\Event::modes() as $value => $label)
                        <option value="{{ $value }}" {{ old('mode', $event->mode) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="image" class="block text-sm font-semibold text-slate-600">Thumbnail Event</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="mt-1 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-xs text-slate-600 file:mr-2 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-700">
                <p class="mt-1 text-xs text-slate-400">Gambar kecil untuk kartu event. Kosongkan jika tidak diganti.</p>
                @if ($event->image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($event->image) }}" alt="{{ $event->title }}"
                         class="mt-2 h-24 w-full rounded-lg object-cover">
                @endif
            </div>
            <div>
                <label for="poster" class="block text-sm font-semibold text-slate-600">Poster Event</label>
                <input type="file" id="poster" name="poster" accept="image/*"
                       class="mt-1 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-xs text-slate-600 file:mr-2 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-700">
                <p class="mt-1 text-xs text-slate-400">Gambar besar untuk halaman detail. Kosongkan jika tidak diganti.</p>
                @if ($event->poster)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($event->poster) }}" alt="{{ $event->title }}"
                         class="mt-2 h-40 w-full rounded-lg object-cover">
                @endif
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-semibold text-slate-600">Deskripsi</label>
                <textarea id="description" name="description" rows="5"
                          class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ $oldInput('description') }}</textarea>
            </div>
        </div>
    </div>

    @include('admin.events._form_builder', [
        'name' => 'form_fields',
        'fields' => $initialForm,
        'label' => 'Form Registrasi Peserta',
    ])

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Form Presensi</h2>
                <p class="mt-0.5 text-sm text-slate-600">Aktifkan untuk mengumpulkan data kehadiran peserta.</p>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
                <input type="checkbox" name="attendance_enabled" value="1" {{ old('attendance_enabled', $event->attendance_enabled) ? 'checked' : '' }}
                       class="peer sr-only" onchange="document.getElementById('attendance-fields-wrap').classList.toggle('hidden', !this.checked)">
                <div class="h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-all peer-checked:bg-emerald-600 peer-checked:after:translate-x-full"></div>
            </label>
        </div>
        <div id="attendance-fields-wrap" class="{{ old('attendance_enabled', $event->attendance_enabled) ? '' : 'hidden' }}">
            @include('admin.events._form_builder', [
                'name' => 'attendance_fields',
                'fields' => $initialAttendance,
                'label' => 'Form Presensi',
            ])
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.events.show', $event) }}"
           class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
            Batal
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Simpan Perubahan
        </button>
    </div>
</form>

@endsection