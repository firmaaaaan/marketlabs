@extends('layouts.admin')

@section('title', 'Pemeriksaan Kesehatan - MarketLabs')

@section('page', 'Pemeriksaan Kesehatan')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Booking Pemeriksaan Kesehatan</h1>
        <p class="mt-1 text-sm text-slate-600">Kelola booking pemeriksaan HbSAg &amp; narkoba: konfirmasi, hasil, dan pembayaran.</p>
    </div>
    <a href="{{ route('admin.health-checkup-types.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        Tarif Pemeriksaan
    </a>
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

{{-- Filter --}}
<form action="{{ route('admin.health-checkups.index') }}" method="GET" class="mt-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-semibold text-slate-500">Status</label>
        <select name="status" class="mt-1 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <option value="">Semua Status</option>
            @foreach (['pending' => 'Menunggu Konfirmasi', 'approved' => 'Terjadwal', 'done' => 'Selesai', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'] as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500">Jenis</label>
        <select name="type" class="mt-1 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <option value="">Semua Jenis</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500">Tanggal Booking</label>
        <input type="date" name="date" value="{{ request('date') }}"
               class="mt-1 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode / nama pasien"
               class="mt-1 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
        Filter
    </button>
    <a href="{{ route('admin.health-checkups.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
        Reset
    </a>
</form>

{{-- Tabel --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Antrian</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pasien</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pembayaran</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($checkups as $checkup)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900">{{ $checkup->queue_label }}</p>
                            <p class="text-xs text-slate-500">{{ $checkup->code }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $checkup->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $checkup->user->institution ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $checkup->type?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $checkup->booking_date->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($checkup->status) {
                                'pending' => 'bg-amber-50 text-amber-700',
                                'approved' => 'bg-sky-50 text-sky-700',
                                'done' => 'bg-emerald-50 text-emerald-700',
                                'rejected' => 'bg-red-50 text-red-600',
                                'cancelled' => 'bg-slate-100 text-slate-500',
                                default => 'bg-slate-100 text-slate-500',
                            } }}">
                                {{ $checkup->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $checkup->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $checkup->payment_status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.health-checkups.show', $checkup) }}"
                               class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada booking pemeriksaan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($checkups->hasPages())
    <div class="mt-6">
        {{ $checkups->links() }}
    </div>
@endif

@endsection
