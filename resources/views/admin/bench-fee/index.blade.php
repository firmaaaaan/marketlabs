@extends('layouts.admin')

@section('title', 'Tarif Bench Fee - MarketLabs')

@section('page', 'Tarif Bench Fee')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Tarif Bench Fee</h1>
        <p class="mt-1 text-sm text-slate-600">Kelola jenjang dan tarif bench fee laboratorium per 3 bulan. Perubahan langsung diterapkan pada form permohonan riset.</p>
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

@if (session('error'))
    <div class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        {{ session('error') }}
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

{{-- Bagian Kelola Jenjang --}}
<div class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-900">Kelola Jenjang</h2>
        <p class="mt-0.5 text-xs text-slate-500">Tambah, edit, atau hapus jenjang penelitian. Jenjang yang dihapus akan menghapus semua tarif terkait.</p>
    </div>

    <div class="px-6 py-4">
        {{-- Form Tambah Jenjang --}}
        <form action="{{ route('admin.bench-fee.levels.store') }}" method="POST" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label for="new_label" class="block text-xs font-semibold text-slate-600">Nama Jenjang</label>
                <input type="text" id="new_label" name="label" required placeholder="contoh: SLTP"
                       class="mt-1 w-48 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="new_sort_order" class="block text-xs font-semibold text-slate-600">Urutan</label>
                <input type="number" id="new_sort_order" name="sort_order" value="{{ $levels->count() }}" min="0"
                       class="mt-1 w-24 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                + Tambah Jenjang
            </button>
        </form>

        {{-- Daftar Jenjang --}}
        @if ($levels->isNotEmpty())
            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Urutan</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Jenjang</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Slug</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($levels as $level)
                            <tr>
                                <form action="{{ route('admin.bench-fee.levels.update', $level) }}" method="POST" class="contents">
                                    @csrf
                                    @method('PATCH')
                                    <td class="px-4 py-2">
                                        <input type="number" name="sort_order" value="{{ $level->sort_order }}" min="0"
                                               class="w-20 rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" name="label" value="{{ $level->label }}" required
                                               class="w-40 rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </td>
                                    <td class="px-4 py-2 text-sm text-slate-500">{{ $level->name }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="submit"
                                                    class="rounded-lg bg-slate-100 p-1.5 text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700"
                                                    title="Simpan">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </button>
                                </form>
                                <form action="{{ route('admin.bench-fee.levels.destroy', $level) }}" method="POST"
                                      data-confirm="Hapus jenjang {{ $level->label }}? Semua tarif terkait akan dihapus." data-confirm-accept="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg bg-red-50 p-1.5 text-red-600 transition hover:bg-red-100"
                                            title="Hapus">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                                        </div>
                                    </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mt-4 text-sm text-slate-500">Belum ada jenjang. Tambahkan jenjang terlebih dahulu.</p>
        @endif
    </div>
</div>

{{-- Bagian Tarif --}}
<form action="{{ route('admin.bench-fee.update') }}" method="POST" class="mt-8">
    @csrf
    @method('PUT')

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-900">Tarif per 3 Bulan</h2>
            <p class="mt-0.5 text-xs text-slate-500">Atur tarif bench fee untuk setiap kombinasi jenjang, instansi, dan kategori.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jenjang</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Instansi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tarif / 3 Bulan (Rp)</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $i = 0; @endphp
                    @foreach ($rates as $level => $types)
                        @foreach ($types as $type => $categories)
                            @foreach ($categories as $category => $rate)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ $level }}</td>
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
                                    <td class="px-6 py-4 text-right">
                                        @php $rateModel = $rateModels->get($level.'|'.$type.'|'.$category); @endphp
                                        @if ($rateModel)
                                            <button type="button" onclick="deleteRate('{{ route('admin.bench-fee.destroy', $rateModel) }}', 'Hapus tarif {{ $level }} - {{ $type === 'dalam' ? 'Dalam' : 'Luar' }} - {{ $category === 'biomedis' ? 'Biomedis' : 'Non-Biomedis' }}?')"
                                                    class="rounded-lg bg-red-50 p-1.5 text-red-600 transition hover:bg-red-100"
                                                    title="Hapus tarif">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endforeach
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
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

@push('scripts')
<script>
    async function deleteRate(url, message) {
        if (!confirm(message)) return;
        try {
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            if (res.ok) {
                window.location.reload();
            }
        } catch (e) {}
    }
</script>
@endpush
