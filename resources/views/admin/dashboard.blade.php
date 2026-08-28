@extends('layouts.admin')

@section('title', 'Dashboard Admin - MarketLabs')

@section('page', 'Dashboard')

@section('content')

{{-- Welcome Banner --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-emerald-500 to-teal-500 p-6 sm:p-8 text-white shadow-lg shadow-emerald-200/50">
    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
    <div class="absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-white/5"></div>
    <div class="relative z-10">
        <h2 class="text-2xl font-extrabold sm:text-3xl">Selamat Datang, {{ Auth::user()->name }} 👋</h2>
        <p class="mt-2 max-w-xl text-sm text-white/80 sm:text-base">Berikut ringkasan aktivitas laboratorium hari ini. Kelola peminjaman, riset, dan pengguna dalam satu panel terpadu.</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('admin.borrowings.index') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/30">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-1.243 1.007-2.25 2.25-2.25h13.5" />
                </svg>
                Peminjaman
            </a>
            <a href="{{ route('admin.research.index') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/30">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                </svg>
                Riset
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/30">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                Pengguna
            </a>
            @endif
        </div>
    </div>
</div>

{{-- Kartu Statistik --}}
<div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['label' => 'Total Pengguna', 'value' => number_format($totalUsers), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'green', 'bg' => 'from-emerald-500 to-emerald-600', 'light' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10', 'border' => 'hover:ring-emerald-500/30'],
        ['label' => 'Total Alat', 'value' => number_format($totalTools), 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4', 'color' => 'emerald', 'bg' => 'from-emerald-500 to-teal-600', 'light' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10', 'border' => 'hover:ring-emerald-500/30'],
        ['label' => 'Peminjaman Berjalan', 'value' => number_format($activeBorrowings), 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'sky', 'bg' => 'from-sky-500 to-blue-600', 'light' => 'bg-sky-50 text-sky-600 ring-sky-600/10', 'border' => 'hover:ring-sky-500/30'],
        ['label' => 'Menunggu Persetujuan', 'value' => number_format($pendingBorrowings), 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'amber', 'bg' => 'from-amber-500 to-orange-600', 'light' => 'bg-amber-50 text-amber-600 ring-amber-600/10', 'border' => 'hover:ring-amber-500/30'],
    ] as $i => $stat)
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 {{ $stat['border'] }}">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $stat['value'] }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $stat['bg'] }} text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="h-1.5 w-full rounded-full bg-slate-100">
                    <div class="h-1.5 rounded-full bg-gradient-to-r {{ $stat['bg'] }}" style="width: {{ max(10, min(100, ($stat['value'] > 0 ? min(100, max(10, (int) str_replace(',', '', $stat['value']) * 3)) : 10))) }}%"></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Kartu Statistik Riset --}}
<div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['label' => 'Total Permohonan Riset', 'value' => number_format($totalRisets), 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'color' => 'indigo', 'bg' => 'from-indigo-500 to-violet-600', 'light' => 'bg-indigo-50 text-indigo-600 ring-indigo-600/10', 'border' => 'hover:ring-indigo-500/30'],
        ['label' => 'Riset Menunggu Persetujuan', 'value' => number_format($pendingRisets), 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'amber', 'bg' => 'from-amber-500 to-orange-600', 'light' => 'bg-amber-50 text-amber-600 ring-amber-600/10', 'border' => 'hover:ring-amber-500/30'],
        ['label' => 'Riset Sedang Berlangsung', 'value' => number_format($ongoingRisets), 'icon' => 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z', 'color' => 'sky', 'bg' => 'from-sky-500 to-blue-600', 'light' => 'bg-sky-50 text-sky-600 ring-sky-600/10', 'border' => 'hover:ring-sky-500/30'],
        ['label' => 'Riset Selesai', 'value' => number_format($doneRisets), 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'green', 'bg' => 'from-emerald-500 to-emerald-600', 'light' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10', 'border' => 'hover:ring-emerald-500/30'],
    ] as $stat)
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 {{ $stat['border'] }}">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $stat['value'] }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $stat['bg'] }} text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="h-1.5 w-full rounded-full bg-slate-100">
                    <div class="h-1.5 rounded-full bg-gradient-to-r {{ $stat['bg'] }}" style="width: {{ max(10, min(100, ($stat['value'] > 0 ? min(100, max(10, (int) str_replace(',', '', $stat['value']) * 3)) : 10))) }}%"></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Quick Actions --}}
<div class="mt-8">
    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Aksi Cepat</h3>
    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.borrowings.index', ['status' => 'pending']) }}"
           class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 hover:border-amber-300">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition group-hover:scale-110">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-900">Review Peminjaman</p>
                <p class="text-xs text-slate-500">{{ $pendingBorrowings }} menunggu</p>
            </div>
        </a>
        <a href="{{ route('admin.research.index', ['status' => 'pending']) }}"
           class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 hover:border-indigo-300">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition group-hover:scale-110">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-900">Review Riset</p>
                <p class="text-xs text-slate-500">{{ $pendingRisets }} menunggu</p>
            </div>
        </a>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.users.index') }}"
           class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 hover:border-emerald-300">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:scale-110">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-900">Kelola Pengguna</p>
                <p class="text-xs text-slate-500">{{ number_format($totalUsers) }} terdaftar</p>
            </div>
        </a>
        @endif
        <a href="{{ route('admin.tools.index') }}"
           class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 hover:border-sky-300">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600 transition group-hover:scale-110">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.384 3.18a1 1 0 01-1.45-1.058L3.5 12.5l5.456 3.367a1 1 0 01.376.41l1.588 4.417a1 1 0 001.766.016l.734-2.602M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-900">Kelola Alat</p>
                <p class="text-xs text-slate-500">{{ number_format($totalTools) }} terdaftar</p>
            </div>
        </a>
    </div>
</div>

{{-- Peminjaman Terbaru --}}
<div class="mt-10">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-900">Peminjaman Terbaru</h3>
        <a href="{{ route('admin.borrowings.index') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-800 transition">
            Lihat Semua →
        </a>
    </div>
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kode</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Peminjam</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Unit</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentBorrowings as $borrowing)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-4 text-sm font-semibold text-emerald-600">
                                <a href="{{ route('admin.borrowings.show', $borrowing) }}" class="transition hover:text-emerald-800 hover:underline">{{ $borrowing->code }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $borrowing->user->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $borrowing->items->sum('quantity') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold {{ match ($borrowing->status) {
                                    'pending' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                                    'approved' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20',
                                    'rejected' => 'bg-red-50 text-red-600 ring-1 ring-inset ring-red-500/20',
                                    'borrowed' => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20',
                                    'returned' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
                                    'cancelled' => 'bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-500/20',
                                    default => 'bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-500/20',
                                } }}">
                                    @switch($borrowing->status)
                                        @case('pending')
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            @break
                                        @case('approved')
                                            <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                            @break
                                        @case('borrowed')
                                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                            @break
                                        @case('returned')
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            @break
                                        @case('rejected')
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            @break
                                        @default
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    @endswitch
                                    {{ \App\Models\Borrowing::statusLabel($borrowing->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <p class="mt-2 text-sm font-semibold text-slate-700">Belum ada peminjaman</p>
                                <p class="mt-1 text-xs text-slate-500">Data peminjaman akan muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Permohonan Riset Terbaru --}}
<div class="mt-10">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-900">Permohonan Riset Terbaru</h3>
        <a href="{{ route('admin.research.index') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-800 transition">
            Lihat Semua →
        </a>
    </div>
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kode</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Pemohon</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Bidang</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentRisets as $proposal)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-4 text-sm font-semibold text-emerald-600">
                                <a href="{{ route('admin.research.show', $proposal) }}" class="transition hover:text-emerald-800 hover:underline">{{ $proposal->code }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $proposal->user->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $proposal->field ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold {{ match ($proposal->status) {
                                    'pending' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
                                    'approved' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20',
                                    'ongoing' => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20',
                                    'rejected' => 'bg-red-50 text-red-600 ring-1 ring-inset ring-red-500/20',
                                    'done' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
                                    'cancelled' => 'bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-500/20',
                                    default => 'bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-500/20',
                                } }}">
                                    @switch($proposal->status)
                                        @case('pending')
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            @break
                                        @case('approved')
                                            <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                            @break
                                        @case('ongoing')
                                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                            @break
                                        @case('done')
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            @break
                                        @case('rejected')
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            @break
                                        @default
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    @endswitch
                                    {{ \App\Models\ResearchProposal::statusLabel($proposal->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <p class="mt-2 text-sm font-semibold text-slate-700">Belum ada permohonan riset</p>
                                <p class="mt-1 text-xs text-slate-500">Data permohonan riset akan muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Ringkasan --}}
<div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ([
        ['label' => 'Total Laboratorium', 'value' => number_format($totalLaboratoriums), 'desc' => 'Laboratorium terdaftar & aktif', 'icon' => 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z', 'color' => 'green'],
        ['label' => 'Riset Lunas', 'value' => number_format($paidRisets), 'desc' => 'Pembayaran permohonan riset lunas', 'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z', 'color' => 'amber'],
        ['label' => 'Peminjaman Selesai', 'value' => number_format($returnedBorrowings), 'desc' => 'Pengembalian alat selesai', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z', 'color' => 'sky'],
    ] as $card)
        <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ match($card['color']) {
                    'green' => 'bg-emerald-50 text-emerald-600',
                    'amber' => 'bg-amber-50 text-amber-600',
                    'sky' => 'bg-sky-50 text-sky-600',
                    default => 'bg-slate-50 text-slate-600',
                } }} transition group-hover:scale-110">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                </div>
            </div>
            <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-400">{{ $card['desc'] }}</p>
        </div>
    @endforeach
</div>

@endsection
