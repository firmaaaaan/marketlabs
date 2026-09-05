@extends('layouts.admin')

@section('title', 'Tarif Pemeriksaan Kesehatan - MarketLabs')

@section('page', 'Tarif Pemeriksaan Kesehatan')

@section('content')

@if (auth()->user()->isSuperAdmin())
<div id="bulk-delete-bar" class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-red-700"><span id="selected-count">0</span> jenis dipilih</p>
        <button type="button" onclick="submitBulkDelete()"
                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
            Hapus Terpilih
        </button>
    </div>
</div>
<form id="bulk-delete-form" action="{{ route('admin.health-checkup-types.bulk-destroy') }}" method="POST">
    @csrf
</form>
@endif

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Jenis &amp; Tarif Pemeriksaan Kesehatan</h1>
        <p class="mt-1 text-sm text-slate-600">
            Kelola jenis pemeriksaan (HbSAg, Narkoba, dll) beserta tarifnya. Jenis nonaktif tidak tampil di katalog publik.
        </p>
    </div>
    <a href="{{ route('admin.health-checkups.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        Kembali ke Booking
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

{{-- Tambah jenis --}}
<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-bold text-slate-900">Tambah Jenis Pemeriksaan Baru</h2>
    <form action="{{ route('admin.health-checkup-types.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2">
        @csrf
        <div>
            <label for="new-key" class="block text-sm font-semibold text-slate-700">Key <span class="text-red-500">*</span></label>
            <input type="text" id="new-key" name="key" value="{{ old('key') }}" required placeholder="Contoh: hbsag"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <p class="mt-1 text-xs text-slate-500">Huruf kecil, tanpa spasi (dipakai untuk tautan katalog).</p>
        </div>
        <div>
            <label for="new-name" class="block text-sm font-semibold text-slate-700">Nama <span class="text-red-500">*</span></label>
            <input type="text" id="new-name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Pemeriksaan HbSAg"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div class="sm:col-span-2">
            <label for="new-description" class="block text-sm font-semibold text-slate-700">Deskripsi</label>
            <textarea id="new-description" name="description" rows="2" maxlength="2000"
                      class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('description') }}</textarea>
        </div>
        <div class="flex items-end gap-6">
            <div>
                <label for="new-price" class="block text-sm font-semibold text-slate-700">Tarif (Rp) <span class="text-red-500">*</span></label>
                <input type="number" id="new-price" name="price" value="{{ old('price') }}" min="0" step="1" required
                       class="mt-1.5 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
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
                + Tambah Jenis
            </button>
        </div>
    </form>
</div>

{{-- Daftar jenis --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    @if (auth()->user()->isSuperAdmin())
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"
                               class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                    </th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tarif</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Booking</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($types as $type)
                    <tr class="transition hover:bg-slate-50">
                        @if (auth()->user()->isSuperAdmin())
                        <td class="w-10 px-4 py-4">
                            <input type="checkbox" name="ids[]" value="{{ $type->id }}" onchange="updateBulkCount()"
                                   class="bulk-checkbox h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                        </td>
                        @endif
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $type->name }}</p>
                            <p class="text-xs text-slate-500">Key: {{ $type->key }}</p>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $type->formatted_price }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $type->checkups_count }} booking</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $type->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $type->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="toggleEdit('{{ $type->id }}')"
                                        class="rounded-lg bg-slate-100 p-1.5 text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700"
                                        title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                <form action="{{ route('admin.health-checkup-types.destroy', $type) }}" method="POST"
                                      data-confirm="Hapus jenis pemeriksaan {{ $type->name }}?" data-confirm-accept="Ya, Hapus">
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
                    <tr id="edit-form-{{ $type->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                        <td colspan="6" class="px-6 py-4">
                            <form action="{{ route('admin.health-checkup-types.update', $type) }}" method="POST" class="grid gap-4 sm:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Key</label>
                                    <input type="text" name="key" value="{{ $type->key }}" required
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Nama</label>
                                    <input type="text" name="name" value="{{ $type->name }}" required
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-500">Deskripsi</label>
                                    <textarea name="description" rows="2" maxlength="2000"
                                              class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ $type->description }}</textarea>
                                </div>
                                <div class="flex flex-wrap items-end gap-6">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Tarif (Rp)</label>
                                        <input type="number" name="price" value="{{ $type->price }}" min="0" required
                                               class="mt-1 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Aktif
                                    </label>
                                </div>
                                <div class="flex items-end justify-end gap-2">
                                <button type="button" onclick="toggleEdit('{{ $type->id }}')"
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
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada jenis pemeriksaan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    @if (auth()->user()->isSuperAdmin())
    function toggleSelectAll(el) {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = el.checked);
        updateBulkCount();
    }
    function updateBulkCount() {
        const checked = document.querySelectorAll('.bulk-checkbox:checked').length;
        document.getElementById('selected-count').textContent = checked;
        document.getElementById('bulk-delete-bar').classList.toggle('hidden', checked === 0);
    }
    function submitBulkDelete() {
        const checked = document.querySelectorAll('.bulk-checkbox:checked');
        if (checked.length === 0) return;
        const form = document.getElementById('bulk-delete-form');
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });
        openBulkModal(checked.length, function () { form.submit(); });
    }
    @endif
    function toggleEdit(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form) form.classList.toggle('hidden');
    }
</script>
@endpush

@endsection
