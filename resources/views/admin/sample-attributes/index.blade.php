@extends('layouts.admin')

@section('title', 'Bentuk & Jenis Sampel - MarketLabs')

@section('page', 'Bentuk & Jenis Sampel')

@section('content')

@if (auth()->user()->isSuperAdmin())
<div id="bulk-delete-bar" class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-red-700"><span id="selected-count">0</span> item dipilih</p>
        <button type="button" onclick="submitBulkDelete()"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
            Hapus Terpilih
        </button>
    </div>
</div>
<form id="bulk-delete-form" action="" method="POST">
    @csrf
</form>
@endif

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Bentuk &amp; Jenis Sampel</h1>
        <p class="mt-1 text-sm text-slate-600">Opsi bentuk (cair, padat, dst.) dan jenis (air, urine, dst.) yang bisa dipilih pemohon saat mengajukan pengujian.</p>
    </div>
    <a href="{{ route('admin.sample-tests.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        Kelola Pengujian
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

<div class="mt-8 grid gap-8 xl:grid-cols-2">

    {{-- Bentuk sampel --}}
    <div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Bentuk Sampel</h2>
            <p class="mt-0.5 text-sm text-slate-500">Contoh: Cair, Padat, Bubuk, Semi Padat, Gas.</p>

            <form action="{{ route('admin.sample-attributes.forms.store') }}" method="POST" class="mt-4">
                @csrf
                <label for="new-form" class="block text-sm font-semibold text-slate-700">Nama Bentuk <span class="text-red-500">*</span></label>
                <div class="mt-1.5 flex flex-wrap gap-3">
                    <input type="text" id="new-form" name="name" value="{{ old('name') }}" required placeholder="Contoh: Cair"
                           class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <button type="submit"
                            class="flex-none rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                        + Tambah
                    </button>
                </div>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </form>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        @if (auth()->user()->isSuperAdmin())
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" class="bulk-select-form h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500" onchange="toggleBulkType(this, 'form')">
                        </th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Bentuk</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Dipakai</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($forms as $form)
                        <tr class="transition hover:bg-slate-50">
                            @if (auth()->user()->isSuperAdmin())
                            <td class="w-10 px-4 py-4">
                                <input type="checkbox" name="ids[]" value="form-{{ $form->id }}" onchange="updateBulkCount()"
                                       class="bulk-checkbox h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                            </td>
                            @endif
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $form->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $form->items_count }} sampel</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $form->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $form->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="toggleEdit('form', {{ $form->id }})"
                                            class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.sample-attributes.forms.destroy', $form) }}" method="POST"
                                          data-confirm="Hapus bentuk {{ $form->name }}?" data-confirm-accept="Ya, Hapus">
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
                        <tr id="edit-form-{{ $form->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                            <td colspan="5" class="px-6 py-4">
                                <form action="{{ route('admin.sample-attributes.forms.update', $form) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Nama</label>
                                        <input type="text" name="name" value="{{ $form->name }}" required
                                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $form->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Aktif
                                    </label>
                                    <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        Simpan
                                    </button>
                                    <button type="button" onclick="toggleEdit('form', {{ $form->id }})"
                                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                                        Batal
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada bentuk sampel.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Jenis sampel --}}
    <div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Jenis Sampel</h2>
            <p class="mt-0.5 text-sm text-slate-500">Contoh: Air, Urine, Serum Darah, Tanah, Kain, Makanan.</p>

            <form action="{{ route('admin.sample-attributes.types.store') }}" method="POST" class="mt-4">
                @csrf
                <label for="new-type" class="block text-sm font-semibold text-slate-700">Nama Jenis <span class="text-red-500">*</span></label>
                <div class="mt-1.5 flex flex-wrap gap-3">
                    <input type="text" id="new-type" name="name" value="{{ old('name') }}" required placeholder="Contoh: Air Sungai"
                           class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <button type="submit"
                            class="flex-none rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                        + Tambah
                    </button>
                </div>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </form>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        @if (auth()->user()->isSuperAdmin())
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" class="bulk-select-form h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500" onchange="toggleBulkType(this, 'type')">
                        </th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Dipakai</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($types as $type)
                        <tr class="transition hover:bg-slate-50">
                            @if (auth()->user()->isSuperAdmin())
                            <td class="w-10 px-4 py-4">
                                <input type="checkbox" name="ids[]" value="type-{{ $type->id }}" onchange="updateBulkCount()"
                                       class="bulk-checkbox h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                            </td>
                            @endif
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $type->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $type->items_count }} sampel</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $type->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $type->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="toggleEdit('type', {{ $type->id }})"
                                            class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.sample-attributes.types.destroy', $type) }}" method="POST"
                                          data-confirm="Hapus jenis {{ $type->name }}?" data-confirm-accept="Ya, Hapus">
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
                        <tr id="edit-type-{{ $type->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                            <td colspan="5" class="px-6 py-4">
                                <form action="{{ route('admin.sample-attributes.types.update', $type) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Nama</label>
                                        <input type="text" name="name" value="{{ $type->name }}" required
                                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Aktif
                                    </label>
                                    <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        Simpan
                                    </button>
                                    <button type="button" onclick="toggleEdit('type', {{ $type->id }})"
                                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                                        Batal
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada jenis sampel.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function toggleEdit(kind, id) {
        const form = document.getElementById('edit-' + kind + '-' + id);
        if (form) form.classList.toggle('hidden');
    }
    @if (auth()->user()->isSuperAdmin())
    function toggleBulkType(el, type) {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => {
            if (cb.value.startsWith(type + '-')) cb.checked = el.checked;
        });
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
        const formIds = [];
        const typeIds = [];
        checked.forEach(cb => {
            const [type, id] = cb.value.split('-');
            if (type === 'form') formIds.push(id);
            else if (type === 'type') typeIds.push(id);
        });
        openBulkModal(checked.length, function () {
            if (formIds.length > 0 && typeIds.length > 0) {
                form.action = '{{ route('admin.sample-attributes.forms.bulk-destroy') }}';
                formIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                form.submit();
            } else if (formIds.length > 0) {
                form.action = '{{ route('admin.sample-attributes.forms.bulk-destroy') }}';
                formIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                form.submit();
            } else if (typeIds.length > 0) {
                form.action = '{{ route('admin.sample-attributes.types.bulk-destroy') }}';
                typeIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                form.submit();
            }
        });
    }
    @endif
</script>
@endpush

@endsection
