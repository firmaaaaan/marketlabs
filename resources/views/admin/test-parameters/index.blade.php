@extends('layouts.admin')

@section('title', 'Kelola Parameter - MarketLabs')

@section('page', 'Kelola Parameter')

@section('content')

@if (auth()->user()->isSuperAdmin())
<div id="bulk-delete-bar" class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-red-700"><span id="selected-count">0</span> parameter dipilih</p>
        <button type="button" onclick="submitBulkDelete()"
                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
            Hapus Terpilih
        </button>
    </div>
</div>
<form id="bulk-delete-form" action="{{ route('admin.test-parameters.bulk-destroy') }}" method="POST">
    @csrf
</form>
@endif

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola Parameter Pengujian</h1>
        <p class="mt-1 text-sm text-slate-600">Setiap parameter memiliki satuan tetap dan tarif pengujian.</p>
    </div>
    <a href="{{ route('admin.sample-units.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        Kelola Satuan
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

{{-- Filter --}}
<form action="{{ route('admin.test-parameters.index') }}" method="GET" class="mt-6 flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari nama parameter..."
           class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    <select name="unit_id"
            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        <option value="">Semua Satuan</option>
        @foreach ($units as $unit)
            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
        @endforeach
    </select>
    <button type="submit"
            class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
        Filter
    </button>
    @if (request()->hasAny(['search', 'unit_id']))
        <a href="{{ route('admin.test-parameters.index') }}"
           class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-50">
            Reset
        </a>
    @endif
</form>

{{-- Tambah parameter --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-900">Tambah Parameter</h2>
    </div>
    <form action="{{ route('admin.test-parameters.store') }}" method="POST" class="grid gap-4 px-6 py-6 sm:grid-cols-2 lg:grid-cols-4">
        @csrf
        <div>
            <label for="new-name" class="block text-sm font-semibold text-slate-700">Nama Parameter <span class="text-red-500">*</span></label>
            <input type="text" id="new-name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Uji Kadar Air"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="new-method" class="block text-sm font-semibold text-slate-700">Metode <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
            <input type="text" id="new-method" name="method" value="{{ old('method') }}" placeholder="Contoh: Nelson Somogy"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @error('method')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="new-unit" class="block text-sm font-semibold text-slate-700">Satuan <span class="text-red-500">*</span></label>
            <select id="new-unit" name="unit_id" required
                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <option value="">— Pilih Satuan —</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                @endforeach
            </select>
            @error('unit_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="new-rate" class="block text-sm font-semibold text-slate-700">Tarif (Rp) <span class="text-red-500">*</span></label>
            <input type="number" id="new-rate" name="rate" value="{{ old('rate') }}" required min="0" step="1000"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @error('rate')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="new-image" class="block text-sm font-semibold text-slate-700">Gambar <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
            <input type="file" id="new-image" name="image" accept="image/*"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-600 hover:file:bg-emerald-50">
            @error('image')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="flex cursor-pointer items-center gap-2">
                <span class="text-sm font-semibold text-slate-700">Aktif</span>
                <input type="checkbox" name="is_active" value="1" checked class="peer sr-only">
                <span class="relative h-6 w-11 flex-none rounded-full bg-slate-300 transition after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:bg-emerald-600 peer-checked:after:translate-x-5"></span>
            </label>
        </div>
        <div class="flex items-end">
            <button type="submit"
                    class="w-full rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                + Tambah Parameter
            </button>
        </div>
        <div class="sm:col-span-2 lg:col-span-4">
            <label for="new-description" class="block text-sm font-semibold text-slate-700">Deskripsi <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
            <textarea id="new-description" name="description" rows="2"
                      class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('description') }}</textarea>
        </div>
    </form>
</div>

{{-- Daftar parameter --}}
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
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Parameter</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Satuan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tarif</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($parameters as $parameter)
                    <tr class="transition hover:bg-slate-50">
                        @if (auth()->user()->isSuperAdmin())
                        <td class="w-10 px-4 py-4">
                            <input type="checkbox" name="ids[]" value="{{ $parameter->id }}" onchange="updateBulkCount()"
                                   class="bulk-checkbox h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                        </td>
                        @endif
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if ($parameter->image)
                                    <img src="{{ asset('storage/' . $parameter->image) }}" alt="{{ $parameter->name }}"
                                         class="h-10 w-10 flex-none rounded-lg object-cover">
                                @endif
                                <div>
                                    <p class="font-medium text-slate-900">{{ $parameter->name }}</p>
                                    @if ($parameter->method)
                                        <p class="text-xs text-slate-500">Metode: {{ $parameter->method }}</p>
                                    @endif
                                    @if ($parameter->description)
                                        <p class="mt-0.5 max-w-xs truncate text-xs text-slate-500">{{ $parameter->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $parameter->unit->name ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $parameter->formatted_rate }}</td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.test-parameters.toggle', $parameter) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="flex cursor-pointer items-center gap-2" title="{{ $parameter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <input type="checkbox" name="is_active" value="1" {{ $parameter->is_active ? 'checked' : '' }}
                                           onchange="this.form.submit()" class="peer sr-only">
                                    <span class="relative h-6 w-11 flex-none rounded-full bg-slate-300 transition after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:bg-emerald-600 peer-checked:after:translate-x-5"></span>
                                    <span class="text-xs font-semibold {{ $parameter->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $parameter->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </label>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="toggleEdit('{{ $parameter->id }}')"
                                        class="rounded-lg bg-slate-100 p-1.5 text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700"
                                        title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                <form action="{{ route('admin.test-parameters.destroy', $parameter) }}" method="POST"
                                      data-confirm="Hapus parameter {{ $parameter->name }}?" data-confirm-accept="Ya, Hapus">
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
                    <tr id="edit-form-{{ $parameter->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                        <td colspan="6" class="px-6 py-4">
                            <form action="{{ route('admin.test-parameters.update', $parameter) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Nama</label>
                                    <input type="text" name="name" value="{{ $parameter->name }}" required
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Metode</label>
                                    <input type="text" name="method" value="{{ $parameter->method }}" placeholder="Opsional"
                                           class="mt-1 w-40 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Satuan</label>
                                    <select name="unit_id" required
                                            class="mt-1 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}" {{ $parameter->unit_id === $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Tarif (Rp)</label>
                                    <input type="number" name="rate" value="{{ $parameter->rate }}" required min="0" step="1000"
                                           class="mt-1 w-36 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Gambar</label>
                                    <input type="file" name="image" accept="image/*"
                                           class="mt-1 w-48 rounded-lg border border-slate-300 px-2 py-1.5 text-xs text-slate-700 file:rounded-lg file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-slate-600 hover:file:bg-emerald-50">
                                </div>
                                <label class="flex cursor-pointer items-center gap-2 pb-2.5 text-sm text-slate-700">
                                    <span class="font-semibold">Aktif</span>
                                    <input type="checkbox" name="is_active" value="1" {{ $parameter->is_active ? 'checked' : '' }} class="peer sr-only">
                                    <span class="relative h-6 w-11 flex-none rounded-full bg-slate-300 transition after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:bg-emerald-600 peer-checked:after:translate-x-5"></span>
                                </label>
                                <div class="w-full">
                                    <label class="block text-xs font-semibold text-slate-500">Deskripsi</label>
                                    <textarea name="description" rows="2" maxlength="1000"
                                              class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ $parameter->description }}</textarea>
                                </div>
                                <button type="submit"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    Simpan
                                </button>
                                <button type="button" onclick="toggleEdit('{{ $parameter->id }}')"
                                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                                    Batal
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada parameter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($parameters->hasPages())
        <div class="border-t border-slate-100 px-6 py-4">
            {{ $parameters->links() }}
        </div>
    @endif
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
