@extends('layouts.admin')

@section('title', 'Pengaturan Tampilan - MarketLabs')

@section('page', 'Pengaturan Tampilan')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pengaturan Tampilan Katalog</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Atur jumlah card yang ditampilkan pada section katalog di halaman utama (landing page).
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

<form action="{{ route('admin.display-settings.update') }}" method="POST" class="mt-8 max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    {{-- Section Katalog Alat --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h2 class="text-base font-bold text-slate-900">Section Katalog Alat</h2>
        <p class="mt-0.5 text-sm text-slate-600">Jumlah card alat (kesehatan & non-kesehatan) yang ditampilkan di halaman utama.</p>

        <div class="mt-6 space-y-4">
            <label class="flex cursor-pointer items-start justify-between gap-4">
                <span>
                    <span class="block text-sm font-bold text-slate-900">Tampilkan semua alat</span>
                    <span class="mt-0.5 block text-xs text-slate-600">Abaikan batasan jumlah dan tampilkan semua alat aktif.</span>
                </span>
                <span class="relative inline-flex flex-none items-center">
                    <input type="checkbox" name="landing_show_all_tools" value="1" class="peer sr-only"
                           id="toggle_tools" {{ $showAllTools ? 'checked' : '' }}>
                    <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-emerald-600 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                </span>
            </label>

            <div id="tools_count_wrapper" class="{{ $showAllTools ? 'hidden' : '' }}">
                <label for="landing_featured_tools_count" class="block text-sm font-semibold text-slate-700">
                    Jumlah Card <span class="text-red-500">*</span>
                </label>
                <input type="number" id="landing_featured_tools_count" name="landing_featured_tools_count"
                       value="{{ old('landing_featured_tools_count', $featuredToolsCount) }}"
                       min="1" max="50" required
                       class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <p class="mt-1.5 text-xs text-slate-500">
                    Angka 1–50. Default: 5.
                </p>
            </div>
        </div>
    </div>

    {{-- Section Katalog Pengujian --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h2 class="text-base font-bold text-slate-900">Section Katalog Pengujian</h2>
        <p class="mt-0.5 text-sm text-slate-600">Jumlah card parameter pengujian yang ditampilkan di halaman utama.</p>

        <div class="mt-6 space-y-4">
            <label class="flex cursor-pointer items-start justify-between gap-4">
                <span>
                    <span class="block text-sm font-bold text-slate-900">Tampilkan semua pengujian</span>
                    <span class="mt-0.5 block text-xs text-slate-600">Abaikan batasan jumlah dan tampilkan semua parameter aktif.</span>
                </span>
                <span class="relative inline-flex flex-none items-center">
                    <input type="checkbox" name="landing_show_all_parameters" value="1" class="peer sr-only"
                           id="toggle_parameters" {{ $showAllParameters ? 'checked' : '' }}>
                    <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-emerald-600 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                </span>
            </label>

            <div id="parameters_count_wrapper" class="{{ $showAllParameters ? 'hidden' : '' }}">
                <label for="landing_featured_parameters_count" class="block text-sm font-semibold text-slate-700">
                    Jumlah Card <span class="text-red-500">*</span>
                </label>
                <input type="number" id="landing_featured_parameters_count" name="landing_featured_parameters_count"
                       value="{{ old('landing_featured_parameters_count', $featuredParametersCount) }}"
                       min="1" max="50" required
                       class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <p class="mt-1.5 text-xs text-slate-500">
                    Angka 1–50. Default: 5.
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
    document.getElementById('toggle_tools').addEventListener('change', function() {
        document.getElementById('tools_count_wrapper').classList.toggle('hidden', this.checked);
    });
    document.getElementById('toggle_parameters').addEventListener('change', function() {
        document.getElementById('parameters_count_wrapper').classList.toggle('hidden', this.checked);
    });
</script>
@endpush

@endsection
