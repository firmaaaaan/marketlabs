@extends('layouts.admin')

@section('title', 'Kelola Pengujian Sampel - MarketLabs')

@section('page', 'Kelola Pengujian')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pengujian Sampel</h1>
        <p class="mt-1 text-sm text-slate-600">Proses pengajuan, input hasil, dan kelola pembayaran pengujian sampel.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.sample-tests.create') }}"
           class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
            + Buat Pengujian
        </a>
        <a href="{{ route('admin.test-parameters.index') }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
            Kelola Parameter
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
            <option value="received">Sampel Diterima</option>
            <option value="testing">Sedang Diuji</option>
            <option value="done">Selesai</option>
            <option value="rejected">Ditolak</option>
        </select>
        <button type="button" onclick="submitBulkStatus()"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
            Terapkan
        </button>
    </div>
</div>

<form id="bulk-status-form" action="{{ route('admin.sample-tests.bulk-status') }}" method="POST">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" id="bulk-status-input">
    <input type="hidden" name="ids" id="bulk-ids">
</form>

{{-- Filter --}}
<form action="{{ route('admin.sample-tests.index') }}" method="GET" class="mt-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="mb-1 block text-xs font-semibold text-slate-500">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Kode, sampel, atau pemohon..."
               class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>
    <div>
        <label class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
        <select name="status"
                class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <option value="">Semua Status</option>
            @foreach ([
                'pending' => 'Menunggu Persetujuan',
                'approved' => 'Disetujui',
                'received' => 'Sampel Diterima',
                'testing' => 'Sedang Diuji',
                'done' => 'Selesai',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan',
            ] as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-xs font-semibold text-slate-500">Pembayaran</label>
        <select name="payment"
                class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <option value="">Semua</option>
            <option value="paid" {{ request('payment') === 'paid' ? 'selected' : '' }}>Lunas</option>
            <option value="unpaid" {{ request('payment') === 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
        </select>
    </div>
    <button type="submit"
            class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
        Filter
    </button>
    @if (request()->hasAny(['search', 'status', 'payment']))
        <a href="{{ route('admin.sample-tests.index') }}"
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
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pengujian</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pemohon</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pembayaran</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($tests as $test)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-4 py-4 text-center"><input type="checkbox" class="bulk-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" value="{{ $test->id }}" onchange="updateBulkBar()"></td>
                        <td class="px-6 py-4">
                            <p class="text-xs font-bold text-slate-500">{{ $test->code }}</p>
                            <p class="mt-0.5 text-sm font-medium text-slate-900">
                                {{ $test->items->first()?->sample_name ?? 'Pengujian Sampel' }}
                                @if ($test->items->count() > 1)
                                    <span class="text-xs font-normal text-slate-400">+ {{ $test->items->count() - 1 }} sampel lain</span>
                                @endif
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $test->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $test->user->email }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $test->items->count() }} sampel · {{ $test->services_count }} layanan</td>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $test->formatted_total_cost }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($test->status) {
                                'pending' => 'bg-amber-50 text-amber-700',
                                'approved' => 'bg-sky-50 text-sky-700',
                                'received' => 'bg-teal-50 text-teal-700',
                                'testing' => 'bg-indigo-50 text-indigo-700',
                                'done' => 'bg-emerald-50 text-emerald-700',
                                'rejected' => 'bg-red-50 text-red-600',
                                'cancelled' => 'bg-slate-100 text-slate-500',
                                default => 'bg-slate-100 text-slate-500',
                            } }}">
                                {{ $test->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $test->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $test->payment_status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.sample-tests.show', $test) }}"
                               class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada pengujian sampel.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($tests->hasPages())
        <div class="border-t border-slate-100 px-6 py-4">
            {{ $tests->links() }}
        </div>
    @endif
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
    if (checked.length === 0) { alert('Pilih minimal satu pengujian.'); return; }

    const ids = Array.from(checked).map(cb => cb.value);
    document.getElementById('bulk-ids').value = JSON.stringify(ids);
    document.getElementById('bulk-status-input').value = status;
    document.getElementById('bulk-status-form').submit();
}
</script>

@endsection
