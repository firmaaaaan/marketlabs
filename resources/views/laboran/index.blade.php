@extends('layouts.staff')

@section('title', 'Halaman Laboran - MarketLabs')
@section('page', 'Halaman Laboran')

@section('content')

{{-- Sapaan --}}
<section class="rounded-3xl bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-900 p-8 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold text-emerald-100 backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Dashboard Laboran
            </span>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                Selamat datang, {{ $laboran->name }}
            </h1>
            <p class="mt-3 max-w-2xl text-emerald-50/85">
                Berikut ringkasan riset &amp; pengujian yang ditugaskan kepada Anda.
            </p>
        </div>
    </div>
</section>

{{-- Statistik --}}
<div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ([
        ['label' => 'Total Riset', 'value' => $totalRisets, 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'bg-indigo-50 text-indigo-600'],
        ['label' => 'Total Pengujian', 'value' => $totalTests, 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z', 'color' => 'bg-emerald-50 text-emerald-600'],
        ['label' => 'Riset Aktif', 'value' => $activeRisets, 'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z', 'color' => 'bg-sky-50 text-sky-600'],
        ['label' => 'Pengujian Aktif', 'value' => $activeTests, 'icon' => 'M4.5 12.75l6 6 9-13.5', 'color' => 'bg-sky-50 text-sky-600'],
        ['label' => 'Menunggu Persetujuan', 'value' => $pendingRisets + $pendingTests, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-amber-50 text-amber-600'],
        ['label' => 'Belum Dibayar', 'value' => $unpaidTests, 'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z', 'color' => 'bg-rose-50 text-rose-600'],
        ['label' => 'Total Pemeriksaan', 'value' => $totalCheckups, 'icon' => 'M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3.75V7.5m0 0v3.75m-3-3.75h6', 'color' => 'bg-emerald-50 text-emerald-600'],
        ['label' => 'Pemeriksaan Aktif', 'value' => $activeCheckups, 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-sky-50 text-sky-600'],
        ['label' => 'Pemeriksaan Belum Dibayar', 'value' => $unpaidCheckups, 'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z', 'color' => 'bg-rose-50 text-rose-600'],
    ] as $stat)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stat['value'] }}</p>
                </div>
                <span class="flex h-12 w-12 items-center justify-center rounded-xl {{ $stat['color'] }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                    </svg>
                </span>
            </div>
        </div>
    @endforeach
</div>

{{-- Riset & Penelitian --}}
<section class="py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Riset &amp; Penelitian</h2>
                <p class="mt-1 text-sm text-slate-600">Permohonan riset yang ditugaskan kepada Anda.</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ $risets->total() }} riset</span>
        </div>

        {{-- Filter & pencarian riset --}}
        <form method="GET" action="{{ route('laboran.index') }}"
              class="mt-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="min-w-48 flex-1">
                <label for="riset_search" class="block text-xs font-semibold text-slate-500">Cari riset</label>
                <input type="text" id="riset_search" name="riset_search" value="{{ request('riset_search') }}"
                       placeholder="Kode, judul, atau nama pemohon..."
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="riset_status" class="block text-xs font-semibold text-slate-500">Status</label>
                <select id="riset_status" name="riset_status"
                        class="mt-1.5 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">Semua Status</option>
                    @foreach ($risetStatuses as $value => $label)
                        <option value="{{ $value }}" {{ request('riset_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Terapkan
            </button>
            @if (request('riset_status') || request('riset_search'))
                <a href="{{ route('laboran.index') }}"
                   class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tabel riset --}}
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Judul</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pemohon</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Laboratorium</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Periode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($risets as $riset)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-emerald-600">{{ $riset->code }}</td>
                                <td class="max-w-xs px-5 py-4">
                                    <p class="truncate font-semibold text-slate-900">{{ $riset->title }}</p>
                                </td>
                                <td class="max-w-[11rem] px-5 py-4">
                                    <p class="truncate font-medium text-slate-800">{{ $riset->user->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $riset->institution ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $riset->laboratorium?->name ?? 'Belum ditentukan' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ $riset->start_date?->translatedFormat('d M Y') ?? '-' }}<br>
                                    <span class="text-xs text-slate-400">s.d. {{ $riset->end_date?->translatedFormat('d M Y') ?? '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($riset->status) {
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'approved' => 'bg-sky-50 text-sky-700',
                                        'ongoing' => 'bg-indigo-50 text-indigo-700',
                                        'rejected' => 'bg-red-50 text-red-600',
                                        'done' => 'bg-emerald-50 text-emerald-700',
                                        'cancelled' => 'bg-slate-100 text-slate-500',
                                        default => 'bg-slate-100 text-slate-500',
                                    } }}">
                                        {{ $riset->status_label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <a href="{{ route('research.show', $riset) }}"
                                       class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Tidak ada riset yang cocok dengan filter / pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($risets->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $risets->links() }}</div>
            @endif
        </div>
    </div>
</section>

{{-- Pengujian Sampel --}}
<section class="bg-slate-50 py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Pengujian Sampel</h2>
                <p class="mt-1 text-sm text-slate-600">Pengujian sampel yang ditugaskan kepada Anda.</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ $tests->total() }} pengujian</span>
        </div>

        {{-- Filter & pencarian pengujian --}}
        <form method="GET" action="{{ route('laboran.index') }}"
              class="mt-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="min-w-48 flex-1">
                <label for="test_search" class="block text-xs font-semibold text-slate-500">Cari pengujian</label>
                <input type="text" id="test_search" name="test_search" value="{{ request('test_search') }}"
                       placeholder="Kode, nama pemohon, atau layanan..."
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="test_status" class="block text-xs font-semibold text-slate-500">Status</label>
                <select id="test_status" name="test_status"
                        class="mt-1.5 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">Semua Status</option>
                    @foreach ($testStatuses as $value => $label)
                        <option value="{{ $value }}" {{ request('test_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Terapkan
            </button>
            @if (request('test_status') || request('test_search'))
                <a href="{{ route('laboran.index') }}"
                   class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tabel pengujian --}}
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pemohon</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Layanan &amp; Sampel</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pembayaran</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($tests as $test)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-emerald-600">{{ $test->code }}</td>
                                <td class="max-w-[11rem] px-5 py-4">
                                    <p class="truncate font-medium text-slate-800">{{ $test->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $test->delivery_method_label }}</p>
                                </td>
                                <td class="max-w-xs px-5 py-4">
                                    <p class="text-slate-600">{{ $test->total_samples }} sampel · {{ $test->services_count }} layanan</p>
                                    @if ($test->items->isNotEmpty())
                                        <p class="mt-0.5 truncate text-xs text-slate-400">
                                            {{ $test->items->groupBy('parameter_id')->keys()->map(fn ($id) => $test->items->firstWhere('parameter_id', $id)?->parameter?->name)->filter()->implode(', ') }}
                                        </p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
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
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $test->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $test->payment_status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if (! in_array($test->status, ['done', 'rejected', 'cancelled']))
                                            <form action="{{ route('laboran.tests.status', $test) }}" method="POST" class="flex items-center gap-1">
                                                @csrf
                                                <select name="status"
                                                        class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                    @foreach ([
                                                        'approved' => 'Disetujui',
                                                        'received' => 'Sampel Diterima',
                                                        'testing' => 'Sedang Diuji',
                                                        'done' => 'Selesai',
                                                        'rejected' => 'Ditolak',
                                                    ] as $value => $label)
                                                        <option value="{{ $value }}" {{ $test->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit"
                                                        class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                    Simpan
                                                </button>
                                            </form>
                                            <form action="{{ route('laboran.tests.payment', $test) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="payment_status" value="{{ $test->is_paid ? 'unpaid' : 'paid' }}">
                                                <button type="submit"
                                                        class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $test->is_paid ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                    {{ $test->is_paid ? 'Bayar' : 'Lunas' }}
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('laboran.tests.print', $test) }}" target="_blank"
                                           class="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                            Cetak
                                        </a>
                                        <a href="{{ route('sample-tests.show', $test) }}"
                                           class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Tidak ada pengujian yang cocok dengan filter / pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($tests->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $tests->links() }}</div>
            @endif
        </div>
    </div>
</section>

{{-- Pemeriksaan Kesehatan --}}
<section class="py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Pemeriksaan Kesehatan</h2>
                <p class="mt-1 text-sm text-slate-600">Antrian booking pemeriksaan yang ditugaskan kepada Anda sebagai pemeriksa.</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ $checkups->total() }} pemeriksaan</span>
        </div>

        {{-- Filter status --}}
        <form method="GET" action="{{ route('laboran.index') }}"
              class="mt-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div>
                <label for="checkup_status" class="block text-xs font-semibold text-slate-500">Status</label>
                <select id="checkup_status" name="checkup_status"
                        class="mt-1.5 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">Semua Status</option>
                    @foreach ($checkupStatuses as $value => $label)
                        <option value="{{ $value }}" {{ request('checkup_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Terapkan
            </button>
            @if (request('checkup_status'))
                <a href="{{ route('laboran.index') }}"
                   class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600">
                    Reset
                </a>
            @endif
            @if ($schedule['enabled'])
                <span class="ml-auto rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600">
                    Jam layanan: {{ $schedule['open_time'] }}–{{ $schedule['close_time'] }} · ±{{ $schedule['duration'] }} menit/orang
                </span>
            @endif
        </form>

        {{-- Daftar antrian --}}
        <div class="mt-6 space-y-5">
            @forelse ($checkups as $checkup)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 flex-none items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-sm font-extrabold text-white shadow-md">
                                {{ $checkup->queue_label }}
                            </span>
                            <div>
                                <p class="text-xs font-bold text-slate-500">{{ $checkup->code }}</p>
                                <h3 class="mt-0.5 font-bold text-slate-900">{{ $checkup->type?->name ?? 'Pemeriksaan' }}</h3>
                                <p class="mt-0.5 text-sm text-slate-600">
                                    {{ $checkup->booking_date->translatedFormat('l, d M Y') }} · {{ $checkup->user->name }}
                                    <span class="text-slate-400">({{ $checkup->user->institution ?? '-' }})</span>
                                </p>
                                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                    @php
                                        $q = $queues[$checkup->booking_date->toDateString()][$checkup->id] ?? null;
                                    @endphp
                                    @if ($q && $q['position'] !== null)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2.5 py-0.5 text-[11px] font-bold text-sky-700">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Posisi: ke-{{ $q['position'] }} dari {{ $q['waiting'] }}
                                        </span>
                                    @endif
                                    @if ($checkup->examiner)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                            {{ $checkup->examiner->name }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-700">Belum ada pemeriksa</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
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
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $checkup->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $checkup->payment_status_label }}
                            </span>
                            @if (in_array($checkup->status, ['pending', 'approved']))
                                <button type="button" onclick="toggleProcess({{ $checkup->id }})"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                    Proses
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($checkup->result)
                        <div class="mt-4 rounded-xl bg-emerald-50 px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Hasil</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $checkup->result }}</p>
                            @if ($checkup->result_notes)
                                <p class="mt-0.5 whitespace-pre-line text-xs text-slate-500">{{ $checkup->result_notes }}</p>
                            @endif
                            @if ($checkup->result_file)
                                <a href="{{ route('health-checkups.result-download', $checkup) }}" target="_blank"
                                   class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 transition hover:text-emerald-800">
                                    📄 Lihat file hasil
                                </a>
                            @endif
                        </div>
                    @endif

                    {{-- Form proses (inline) --}}
                    @if (in_array($checkup->status, ['pending', 'approved']))
                        <div id="process-form-{{ $checkup->id }}" class="mt-5 hidden rounded-xl border border-emerald-200 bg-emerald-50/60 p-5">
                            <p class="text-sm font-bold text-slate-900">Proses Pemeriksaan {{ $checkup->queue_label }}</p>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <form action="{{ route('laboran.health-checkups.result', $checkup) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Hasil <span class="text-red-500">*</span></label>
                                        <input type="text" name="result" list="result-suggestions-{{ $checkup->id }}" maxlength="255" required
                                               placeholder="{{ $checkup->type?->key === 'hbsag' ? 'Negatif / Reaktif' : 'Negatif / Positif' }}"
                                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                        <datalist id="result-suggestions-{{ $checkup->id }}">
                                            @if ($checkup->type?->key === 'hbsag')
                                                <option value="Negatif"></option>
                                                <option value="Reaktif"></option>
                                            @else
                                                <option value="Negatif"></option>
                                                <option value="Positif"></option>
                                            @endif
                                        </datalist>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Keterangan</label>
                                        <textarea name="result_notes" rows="2" maxlength="2000"
                                                  class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">File Hasil <span class="font-normal text-slate-400">(opsional)</span></label>
                                        <input type="file" name="result_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                               class="mt-1 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-xs text-slate-600 file:mr-2 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-700 focus:outline-none">
                                    </div>
                                    <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                        Simpan Hasil
                                    </button>
                                </form>

                                <div class="flex flex-col justify-between gap-3 rounded-xl border border-dashed border-emerald-300 bg-white/60 p-4">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-600">Selesai diproses?</p>
                                        <p class="mt-1 text-xs text-slate-500">Tandai pemeriksaan selesai. Hasil wajib diisi terlebih dahulu (jika ada).</p>
                                    </div>
                                    <form action="{{ route('laboran.health-checkups.status', $checkup) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="done">
                                        <button type="submit"
                                                class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">
                                            Tandai Selesai
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-white/60 p-4">
                                <form action="{{ route('laboran.health-checkups.status', $checkup) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status"
                                            class="rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                        @foreach (['approved' => 'Disetujui', 'done' => 'Selesai', 'rejected' => 'Ditolak'] as $value => $label)
                                            <option value="{{ $value }}" {{ $checkup->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                        Simpan Status
                                    </button>
                                </form>

                                <form action="{{ route('laboran.health-checkups.payment', $checkup) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="payment_status" value="{{ $checkup->is_paid ? 'unpaid' : 'paid' }}">
                                    <button type="submit"
                                            class="rounded-lg border px-4 py-2 text-xs font-semibold transition {{ $checkup->is_paid ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                        {{ $checkup->is_paid ? 'Tandai Belum Dibayar' : 'Tandai Lunas' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <p class="text-lg font-semibold text-slate-700">Tidak ada pemeriksaan</p>
                    <p class="mt-1 text-sm text-slate-500">Booking pemeriksaan yang ditugaskan kepada Anda akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        @if ($checkups->hasPages())
            <div class="mt-8">
                {{ $checkups->links() }}
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
    function toggleProcess(id) {
        const form = document.getElementById('process-form-' + id);
        if (form) form.classList.toggle('hidden');
    }
</script>
@endpush

@endsection
