@extends('layouts.admin')

@section('title', 'Log Aktivitas - MarketLabs')

@section('page', 'Log Aktivitas')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Log Aktivitas</h1>
        <p class="mt-1 text-sm text-slate-600">
            Riwayat aktivitas yang dilakukan oleh admin dan laboran. Hanya admin yang dapat melihat halaman ini.
        </p>
    </div>
</div>

{{-- Filter --}}
<form action="{{ route('admin.activity-logs.index') }}" method="GET" class="mt-6 flex flex-wrap items-center gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari deskripsi atau nama..."
           class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    <select name="role"
            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        <option value="">Semua Role</option>
        @foreach (\App\Models\User::roles() as $value => $label)
            @if (in_array($value, ['admin', 'laboran'], true))
                <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endif
        @endforeach
    </select>
    <select name="action"
            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        <option value="">Semua Aksi</option>
        @foreach ($actionLabels as $value => $label)
            <option value="{{ $value }}" {{ request('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}"
           class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    <span class="text-sm text-slate-500">s.d.</span>
    <input type="date" name="to" value="{{ request('to') }}"
           class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    <button type="submit"
            class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
        Filter
    </button>
    <a href="{{ route('admin.activity-logs.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        Reset
    </a>
</form>

{{-- Tabel --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pelaku</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($logs as $log)
                    <tr class="transition hover:bg-slate-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                            {{ $log->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $log->user_name ?? ($log->user?->name ?? 'Pengguna terhapus') }}</p>
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $log->role === 'admin' ? 'bg-emerald-50 text-emerald-700' : 'bg-sky-50 text-sky-700' }}">
                                {{ \App\Models\User::roleLabel($log->role ?? '') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($log->action) {
                                'create' => 'bg-emerald-50 text-emerald-700',
                                'update' => 'bg-amber-50 text-amber-700',
                                'delete' => 'bg-red-50 text-red-600',
                                default => 'bg-slate-100 text-slate-600',
                            } }}">
                                {{ $actionLabels[$log->action] ?? $log->action }}
                            </span>
                        </td>
                        <td class="max-w-md px-6 py-4">
                            <p class="text-sm text-slate-700">{{ $log->description }}</p>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 font-mono text-xs text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada log aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $logs->links() }}
</div>

@endsection