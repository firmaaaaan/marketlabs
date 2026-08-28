@extends('layouts.admin')

@section('title', 'Kelola Peminjaman - MarketLabs')

@section('page', 'Kelola Peminjaman')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Daftar Peminjaman</h1>
        <p class="mt-1 text-sm text-slate-600">Setujui, tolak, dan kelola peminjaman alat.</p>
    </div>
    <a href="{{ route('admin.borrowings.export-excel', request()->query()) }}"
       class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        Export Excel
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
<form action="{{ route('admin.borrowings.index') }}" method="GET" class="mt-6 flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari kode atau nama peminjam..."
           class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    <select name="status"
            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        <option value="">Semua Status</option>
        @foreach (['pending', 'approved', 'rejected', 'borrowed', 'returned', 'cancelled'] as $status)
            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                {{ \App\Models\Borrowing::statusLabel($status) }}
            </option>
        @endforeach
    </select>
    <button type="submit"
            class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
        Filter
    </button>
</form>

{{-- Tabel --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Peminjam</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pembayaran</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($borrowings as $borrowing)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm font-semibold text-emerald-600">{{ $borrowing->code }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900">{{ $borrowing->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $borrowing->borrow_date->translatedFormat('d M Y') }} — {{ $borrowing->return_date->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $borrowing->items->sum('quantity') }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($borrowing->status) {
                                'pending' => 'bg-amber-50 text-amber-700',
                                'approved' => 'bg-sky-50 text-sky-700',
                                'rejected' => 'bg-red-50 text-red-600',
                                'borrowed' => 'bg-indigo-50 text-indigo-700',
                                'returned' => 'bg-emerald-50 text-emerald-700',
                                'cancelled' => 'bg-slate-100 text-slate-500',
                                default => 'bg-slate-100 text-slate-500',
                            } }}">
                                {{ \App\Models\Borrowing::statusLabel($borrowing->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $borrowing->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $borrowing->payment_status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.borrowings.invoice', $borrowing) }}" target="_blank"
                                   class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Invoice
                                </a>
                                <a href="{{ route('admin.borrowings.show', $borrowing) }}"
                                   class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada peminjaman.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $borrowings->links() }}
</div>

@endsection
