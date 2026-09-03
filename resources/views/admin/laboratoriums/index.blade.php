@extends('layouts.admin')

@section('title', 'Kelola Laboratorium - MarketLabs')

@section('page', 'Kelola Laboratorium')

@section('content')

@if (auth()->user()->isSuperAdmin())
<div id="bulk-delete-bar" class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-red-700"><span id="selected-count">0</span> laboratorium dipilih</p>
        <button type="button" onclick="submitBulkDelete()"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
            Hapus Terpilih
        </button>
    </div>
</div>
<form id="bulk-delete-form" action="{{ route('admin.laboratoriums.bulk-destroy') }}" method="POST">
    @csrf
</form>
@endif

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola Laboratorium</h1>
        <p class="mt-1 text-sm text-slate-600">Kelola daftar laboratorium yang bisa ditugaskan pada permohonan riset.</p>
    </div>
    <a href="{{ route('admin.laboratoriums.create') }}"
       class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
        + Tambah Laboratorium
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
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Laboratorium</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($laboratoriums as $lab)
                    <tr class="transition hover:bg-slate-50">
                        @if (auth()->user()->isSuperAdmin())
                        <td class="w-10 px-4 py-4">
                            <input type="checkbox" name="ids[]" value="{{ $lab->id }}" onchange="updateBulkCount()"
                                   class="bulk-checkbox h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                        </td>
                        @endif
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $lab->name }}</p>
                            <p class="text-xs text-slate-500">{{ $lab->research_proposals_count }} permohonan riset</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $lab->code ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($lab->description, 60) ?: '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $lab->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $lab->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.laboratoriums.edit', $lab) }}"
                                   class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Edit
                                </a>
                                <form action="{{ route('admin.laboratoriums.destroy', $lab) }}" method="POST"
                                      data-confirm="Hapus laboratorium {{ $lab->name }}?" data-confirm-accept="Ya, Hapus">
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
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada laboratorium.</td>
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
</script>
@endpush

@endsection
