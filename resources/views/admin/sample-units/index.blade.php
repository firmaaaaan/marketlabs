@extends('layouts.admin')

@section('title', 'Kelola Satuan - MarketLabs')

@section('page', 'Kelola Satuan')

@section('content')

@if (auth()->user()->isSuperAdmin())
<div id="bulk-delete-bar" class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-red-700"><span id="selected-count">0</span> satuan dipilih</p>
        <button type="button" onclick="submitBulkDelete()"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
            Hapus Terpilih
        </button>
    </div>
</div>
<form id="bulk-delete-form" action="{{ route('admin.sample-units.bulk-destroy') }}" method="POST">
    @csrf
</form>
@endif

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola Satuan Sampel</h1>
        <p class="mt-1 text-sm text-slate-600">Satuan dipakai oleh parameter pengujian (sampel, meter, running meter, dst).</p>
    </div>
    <a href="{{ route('admin.test-parameters.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        Kelola Parameter
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

{{-- Tambah satuan --}}
<form action="{{ route('admin.sample-units.store') }}" method="POST" class="mt-6">
    @csrf
    <label for="new-name" class="block text-sm font-semibold text-slate-700">Nama Satuan <span class="text-red-500">*</span></label>
    <div class="mt-1.5 flex flex-wrap gap-3">
        <input type="text" id="new-name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Running Meter"
               class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        <input type="text" name="symbol" value="{{ old('symbol') }}" placeholder="Simbol (opsional, mis. rm)"
               class="w-full max-w-[10rem] rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        <button type="submit"
                class="flex-none rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
            + Tambah
        </button>
    </div>
    @error('name')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</form>

{{-- Daftar satuan --}}
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
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Satuan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Simbol</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah Parameter</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($units as $unit)
                    <tr class="transition hover:bg-slate-50">
                        @if (auth()->user()->isSuperAdmin())
                        <td class="w-10 px-4 py-4">
                            <input type="checkbox" name="ids[]" value="{{ $unit->id }}" onchange="updateBulkCount()"
                                   class="bulk-checkbox h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                        </td>
                        @endif
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $unit->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $unit->symbol ?? '-' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $unit->parameters_count }} parameter</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $unit->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="toggleEdit({{ $unit->id }})"
                                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Edit
                                </button>
                                <form action="{{ route('admin.sample-units.destroy', $unit) }}" method="POST"
                                      data-confirm="Hapus satuan {{ $unit->name }}?" data-confirm-accept="Ya, Hapus">
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
                    <tr id="edit-form-{{ $unit->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                        <td colspan="6" class="px-6 py-4">
                            <form action="{{ route('admin.sample-units.update', $unit) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Nama</label>
                                    <input type="text" name="name" value="{{ $unit->name }}" required
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Simbol</label>
                                    <input type="text" name="symbol" value="{{ $unit->symbol }}" placeholder="Opsional"
                                           class="mt-1 w-32 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700">
                                    <input type="checkbox" name="is_active" value="1" {{ $unit->is_active ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    Aktif
                                </label>
                                <button type="submit"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    Simpan
                                </button>
                                <button type="button" onclick="toggleEdit({{ $unit->id }})"
                                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                                    Batal
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada satuan.</td>
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
