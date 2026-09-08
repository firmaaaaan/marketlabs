@extends('layouts.admin')

@section('title', 'Mode Maintenance - MarketLabs')

@section('page', 'Mode Maintenance')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Mode Maintenance</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Aktifkan mode maintenance untuk memblokir akses user. Hanya superadmin yang dapat mengakses sistem saat mode ini aktif.
        </p>
    </div>
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

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

<form action="{{ route('admin.maintenance.update') }}" method="POST" class="mt-8 max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h2 class="text-base font-bold text-slate-900">Pengaturan Maintenance</h2>
        <p class="mt-0.5 text-sm text-slate-600">Ketika diaktifkan, semua user (kecuali superadmin) tidak akan bisa mengakses sistem.</p>

        <div class="mt-6 space-y-4">
            {{-- Toggle Maintenance --}}
            <label class="flex cursor-pointer items-start justify-between gap-4">
                <span>
                    <span class="block text-sm font-bold text-slate-900">Aktifkan Mode Maintenance</span>
                    <span class="mt-0.5 block text-xs text-slate-600">Semua user akan dialihkan ke halaman pemeliharaan.</span>
                </span>
                <span class="relative inline-flex flex-none items-center">
                    <input type="checkbox" name="maintenance_enabled" value="1" class="peer sr-only"
                           id="toggle_maintenance" {{ $enabled ? 'checked' : '' }}>
                    <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-red-600 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                </span>
            </label>

            {{-- Status Indicator --}}
            <div id="maintenance_status" class="flex items-center gap-2 rounded-lg px-4 py-3 {{ $enabled ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                <span class="h-2 w-2 rounded-full {{ $enabled ? 'bg-red-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                <span class="text-sm font-semibold">{{ $enabled ? 'Mode Maintenance AKTIF — User terblokir' : 'Mode Maintenance Nonaktif — Semua user normal' }}</span>
            </div>

            {{-- Maintenance Message --}}
            <div id="maintenance_message_wrapper">
                <label for="maintenance_message" class="block text-sm font-semibold text-slate-700">
                    Pesan Maintenance
                </label>
                <textarea id="maintenance_message" name="maintenance_message" rows="3"
                          class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                          placeholder="Kami sedang melakukan pemeliharaan...">{{ old('maintenance_message', $message) }}</textarea>
                <p class="mt-1.5 text-xs text-slate-500">
                    Pesan yang ditampilkan di halaman maintenance. Kosongkan untuk pesan default.
                </p>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Simpan Pengaturan
        </button>
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('toggle_maintenance').addEventListener('change', function() {
        const status = document.getElementById('maintenance_status');
        if (this.checked) {
            status.className = 'flex items-center gap-2 rounded-lg px-4 py-3 bg-red-50 text-red-700';
            status.innerHTML = '<span class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span><span class="text-sm font-semibold">Mode Maintenance AKTIF — User terblokir</span>';
        } else {
            status.className = 'flex items-center gap-2 rounded-lg px-4 py-3 bg-emerald-50 text-emerald-700';
            status.innerHTML = '<span class="h-2 w-2 rounded-full bg-emerald-500"></span><span class="text-sm font-semibold">Mode Maintenance Nonaktif — Semua user normal</span>';
        }
    });
</script>
@endpush

@endsection
