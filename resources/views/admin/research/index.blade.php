@extends('layouts.admin')

@section('title', 'Kelola Permohonan Riset - MarketLabs')

@section('page', 'Permohonan Riset')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Permohonan Riset &amp; Penelitian</h1>
        <p class="mt-1 text-sm text-slate-600">Kelola permohonan riset &amp; penelitian yang masuk.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.research.export', request()->query()) }}"
           class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>
        <a href="{{ route('admin.research.index', ['status' => 'pending']) }}"
           class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
            Lihat Menunggu Persetujuan
        </a>
    </div>
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

{{-- Bulk Action Bar --}}
<div id="bulk-action-bar" class="mt-6 hidden rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
    <div class="flex flex-wrap items-center gap-3">
        <p class="text-sm font-medium text-emerald-700"><span id="selected-count">0</span> dipilih</p>
        <select id="bulk-status"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <option value="">Pilih Status</option>
            <option value="approved">Disetujui</option>
            <option value="ongoing">Sedang Berlangsung</option>
            <option value="rejected">Ditolak</option>
            <option value="done">Selesai</option>
        </select>
        <button type="button" onclick="submitBulkStatus()"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
            Terapkan
        </button>
    </div>
</div>

<form id="bulk-status-form" action="{{ route('admin.research.bulk-status') }}" method="POST">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" id="bulk-status-input">
    <input type="hidden" name="ids" id="bulk-ids">
</form>

{{-- Filter --}}
<form action="{{ route('admin.research.index') }}" method="GET" class="mt-6 flex flex-wrap items-end gap-3">
    <div>
        <label for="search" class="mb-1 block text-xs font-semibold text-slate-500">Cari</label>
        <input type="text" id="search" name="search" value="{{ request('search') }}"
               placeholder="Kode, judul, atau pemohon..."
               class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>
    <div>
        <label for="status" class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
        <select id="status" name="status"
                class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <option value="">Semua Status</option>
            @foreach ([
                'pending' => 'Menunggu Persetujuan',
                'approved' => 'Disetujui',
                'ongoing' => 'Sedang Berlangsung',
                'rejected' => 'Ditolak',
                'done' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ] as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="date_from" class="mb-1 block text-xs font-semibold text-slate-500">Tanggal Dari</label>
        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>
    <div>
        <label for="date_to" class="mb-1 block text-xs font-semibold text-slate-500">Tanggal Sampai</label>
        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>
    <button type="submit"
            class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
        Filter
    </button>
    @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
        <a href="{{ route('admin.research.index') }}"
           class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-50">
            Reset
        </a>
    @endif
</form>

{{-- Tabel --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-center"><input type="checkbox" id="select-all" onchange="toggleAll(this)" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"></th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Permohonan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pemohon</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Periode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($proposals as $proposal)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-4 py-4 text-center"><input type="checkbox" class="bulk-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" value="{{ $proposal->id }}" onchange="updateBulkBar()"></td>
                        <td class="px-6 py-4">
                            <p class="text-xs font-bold text-slate-500">{{ $proposal->code }}</p>
                            <p class="mt-0.5 max-w-xs truncate text-sm font-medium text-slate-900">{{ $proposal->title }}</p>
                            <p class="text-xs text-slate-500">{{ $proposal->field ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $proposal->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $proposal->user->email }}</p>
                            @if ($proposal->customer_type)
                                <p class="mt-0.5 text-xs font-medium text-emerald-600">{{ $proposal->customer_type_label }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if ($proposal->start_date && $proposal->end_date)
                                {{ $proposal->start_date->translatedFormat('d M Y') }} — {{ $proposal->end_date->translatedFormat('d M Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($proposal->status) {
                                'pending' => 'bg-amber-50 text-amber-700',
                                'approved' => 'bg-sky-50 text-sky-700',
                                'ongoing' => 'bg-indigo-50 text-indigo-700',
                                'rejected' => 'bg-red-50 text-red-600',
                                'done' => 'bg-emerald-50 text-emerald-700',
                                'cancelled' => 'bg-slate-100 text-slate-500',
                                default => 'bg-slate-100 text-slate-500',
                            } }}">
                                {{ $proposal->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end">
                                <a href="{{ route('admin.research.show', $proposal) }}"
                                   class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada permohonan riset.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $proposals->links() }}
</div>

<script>
function toggleAll(el) {
    document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = el.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.bulk-checkbox:checked').length;
    document.getElementById('selected-count').textContent = checked;
    document.getElementById('bulk-action-bar').classList.toggle('hidden', checked === 0);
}

function submitBulkStatus() {
    const status = document.getElementById('bulk-status').value;
    if (!status) { alert('Pilih status terlebih dahulu.'); return; }

    const checked = document.querySelectorAll('.bulk-checkbox:checked');
    if (checked.length === 0) { alert('Pilih minimal satu permohonan riset.'); return; }

    const ids = Array.from(checked).map(cb => cb.value);
    document.getElementById('bulk-ids').value = JSON.stringify(ids);
    document.getElementById('bulk-status-input').value = status;
    document.getElementById('bulk-status-form').submit();
}
</script>

@endsection
