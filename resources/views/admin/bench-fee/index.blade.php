@extends('layouts.admin')

@section('title', 'Tarif Bench Fee - MarketLabs')

@section('page', 'Tarif Bench Fee')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Tarif Bench Fee</h1>
        <p class="mt-1 text-sm text-slate-600">Kelola tarif bench fee laboratorium per 3 bulan berdasarkan jenjang, instansi, dan kategori penelitian. Perubahan langsung diterapkan pada form permohonan riset dan kalkulasi biaya.</p>
    </div>
    <a href="{{ route('admin.research.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        Kembali ke Riset
    </a>
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

<form action="{{ route('admin.bench-fee.update') }}" method="POST" class="mt-8">
    @csrf
    @method('PUT')

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jenjang</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Instansi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tarif / 3 Bulan (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php $i = 0; @endphp
                @foreach ($rates as $level => $types)
                    @foreach ($types as $type => $categories)
                        @foreach ($categories as $category => $rate)
                            <tr>
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $level === 'S2/S3' ? 'S2 / S3' : $level }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $type === 'dalam' ? 'Dalam' : 'Luar' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $category === 'biomedis' ? 'Biomedis' : 'Non-Biomedis' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-slate-500">Rp</span>
                                        <input type="hidden" name="rates[{{ $i }}][level]" value="{{ $level }}">
                                        <input type="hidden" name="rates[{{ $i }}][type]" value="{{ $type }}">
                                        <input type="hidden" name="rates[{{ $i }}][category]" value="{{ $category }}">
                                        <input type="number" name="rates[{{ $i }}][rate]" value="{{ $rate }}" min="0" step="1" required
                                               class="w-40 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                </td>
                            </tr>
                            @php $i++; @endphp
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="mt-3 text-xs text-slate-500">Bench fee = tarif × jumlah periode 3 bulan (dibulatkan ke atas) berdasarkan durasi penelitian.</p>

    <div class="mt-6 flex justify-end">
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Simpan Tarif
        </button>
    </div>
</form>

@endsection
