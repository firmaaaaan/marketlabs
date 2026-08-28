@extends('layouts.admin')

@section('title', 'Kelola Mitra - MarketLabs')

@section('page', 'Kelola Mitra')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola Mitra</h1>
        <p class="mt-1 text-sm text-slate-600">
            Logo mitra tampil di section "Mitra Kami" pada landing page sebagai logo berjalan (marquee).
            Mitra nonaktif tidak akan ditampilkan di halaman publik.
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

{{-- Tambah mitra --}}
<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-bold text-slate-900">Tambah Mitra Baru</h2>
    <form action="{{ route('admin.mitras.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2">
        @csrf
        <div>
            <label for="new-name" class="block text-sm font-semibold text-slate-700">Nama Mitra <span class="text-red-500">*</span></label>
            <input type="text" id="new-name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Universitas Gadjah Mada"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div>
            <label for="new-website" class="block text-sm font-semibold text-slate-700">Website</label>
            <input type="url" id="new-website" name="website" value="{{ old('website') }}" placeholder="https://ugm.ac.id"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div class="sm:col-span-2">
            <label for="new-logo" class="block text-sm font-semibold text-slate-700">URL Logo</label>
            <input type="text" id="new-logo" name="logo" value="{{ old('logo') }}" placeholder="https://logo.clearbit.com/domain.com"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <p class="mt-1 text-xs text-slate-500">Masukkan URL gambar logo (bukan halaman web). Contoh: <code>https://logo.clearbit.com/ugm.ac.id</code></p>
        </div>
        <div class="flex items-end gap-6">
            <div>
                <label for="new-sort_order" class="block text-sm font-semibold text-slate-700">Urutan</label>
                <input type="number" id="new-sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                       class="mt-1.5 w-24 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" checked
                       class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Aktif
            </label>
        </div>
        <div class="flex items-end justify-end">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                + Tambah Mitra
            </button>
        </div>
    </form>
</div>

{{-- Daftar mitra --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Logo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Website</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Urutan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($mitras as $mitra)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4">
                            @if ($mitra->logo_url)
                                <img src="{{ $mitra->logo_url }}" alt="{{ $mitra->name }}" class="h-10 w-auto object-contain">
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-400">
                                    {{ mb_strtoupper(mb_substr($mitra->name, 0, 2)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $mitra->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if ($mitra->website)
                                <a href="{{ $mitra->website }}" target="_blank" class="text-emerald-600 hover:underline">{{ $mitra->website }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $mitra->sort_order }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $mitra->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $mitra->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="toggleEdit({{ $mitra->id }})"
                                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Edit
                                </button>
                                <form action="{{ route('admin.mitras.destroy', $mitra) }}" method="POST"
                                      data-confirm="Hapus mitra {{ $mitra->name }}?" data-confirm-accept="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr id="edit-form-{{ $mitra->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                        <td colspan="6" class="px-6 py-4">
                            <form action="{{ route('admin.mitras.update', $mitra) }}" method="POST" class="grid gap-4 sm:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Nama Mitra</label>
                                    <input type="text" name="name" value="{{ $mitra->name }}" required
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Website</label>
                                    <input type="url" name="website" value="{{ $mitra->website }}" placeholder="https://..."
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-500">URL Logo</label>
                                    <input type="text" name="logo" value="{{ $mitra->logo }}" placeholder="https://example.com/logo.png"
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div class="flex flex-wrap items-end gap-6">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Urutan</label>
                                        <input type="number" name="sort_order" value="{{ $mitra->sort_order }}" min="0"
                                               class="mt-1 w-24 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $mitra->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Aktif
                                    </label>
                                </div>
                                <div class="flex items-end justify-end gap-2">
                                    <button type="button" onclick="toggleEdit({{ $mitra->id }})"
                                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                            Belum ada mitra. Tambahkan mitra pertama melalui form di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function toggleEdit(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form) form.classList.toggle('hidden');
    }
</script>
@endpush

@endsection
