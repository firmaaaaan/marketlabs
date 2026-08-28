@extends('layouts.account')

@section('title', 'Pengujian Sampel - MarketLabs')

@section('account-content')

<div>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pengujian Sampel</h1>
            <p class="mt-1 text-sm text-slate-600">Riwayat pengujian sampel Anda beserta status dan hasilnya.</p>
        </div>
        <a href="{{ route('sample-tests.catalog') }}"
           class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            + Ajukan Pengujian
        </a>
    </div>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-8 space-y-6">
            @forelse ($tests as $test)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-500">{{ $test->code }}</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">
                                {{ $test->items->first()?->sample_name ?? 'Pengujian Sampel' }}
                                @if ($test->items->count() > 1)
                                    <span class="text-sm font-medium text-slate-400">+ {{ $test->items->count() - 1 }} sampel lain</span>
                                @endif
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $test->items->count() }} sampel · {{ $test->services_count }} layanan · {{ $test->units_label }}
                            </p>
                        </div>
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
                    </div>

                    {{-- Tracker status --}}
                    @php
                        $order = ['pending', 'approved', 'received', 'testing', 'done', 'attached'];
                        $steps = [
                            'pending' => 'Diajukan',
                            'approved' => 'Disetujui',
                            'received' => 'Diterima',
                            'testing' => 'Diuji',
                            'done' => 'Selesai',
                            'attached' => 'Hasil Terlampir',
                        ];
                        $icons = [
                            'pending' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                            'approved' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                            'received' => 'M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z',
                            'testing' => 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
                            'done' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                            'attached' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                        ];
                        $hasResult = ! empty($test->result) || ! empty($test->result_file);
                        $reached = array_search($test->status, $order);
                        if ($reached === false) {
                            $reached = 0;
                        }
                        if ($test->status === 'done' && $hasResult) {
                            $reached = count($order) - 1;
                        }
                        $failed = in_array($test->status, ['rejected', 'cancelled']);
                        $times = [
                            'pending' => $test->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'approved' => $test->approved_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'received' => $test->received_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'testing' => $test->tested_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'done' => $test->done_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                        ];
                    @endphp
                    <ol class="mt-5 flex">
                        @foreach ($order as $idx => $key)
                            @php
                                $state = $idx < $reached ? 'done' : ($idx === $reached ? 'current' : 'upcoming');
                                if ($failed && $idx === 0) {
                                    $state = 'failed';
                                }
                                $circle = match ($state) {
                                    'done' => 'bg-emerald-600 text-white border-emerald-600',
                                    'current' => 'bg-white text-emerald-600 border-emerald-600',
                                    'failed' => 'bg-red-500 text-white border-red-500',
                                    default => 'bg-slate-100 text-slate-400 border-slate-200',
                                };
                                $line = $idx < $reached && ! $failed ? 'bg-emerald-500' : 'bg-slate-200';
                            @endphp
                            <li class="flex flex-1 flex-col">
                                <div class="flex items-center">
                                    <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full border-2 {{ $circle }}">
                                        @if ($state === 'failed')
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        @else
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$key] }}" />
                                            </svg>
                                        @endif
                                    </span>
                                    @if (! $loop->last)
                                        <span class="mx-2 h-0.5 flex-1 rounded {{ $line }}"></span>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-xs font-semibold {{ $state === 'upcoming' ? 'text-slate-400' : 'text-slate-900' }}">{{ $steps[$key] }}</p>
                                @if ($key === 'done' && $test->status === 'done' && ! $hasResult)
                                    <p class="mt-0.5 text-[11px] font-semibold text-amber-600">Menunggu hasil</p>
                                @elseif (($times[$key] ?? null))
                                    <p class="mt-0.5 text-[11px] text-slate-400">{{ $times[$key] }} WIB</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @if ($failed)
                        <p class="mt-2 text-xs font-semibold {{ $test->status === 'rejected' ? 'text-red-600' : 'text-slate-500' }}">
                            {{ $test->status === 'rejected' ? 'Pengujian ditolak.' : 'Pengujian dibatalkan.' }}
                        </p>
                    @endif

                    @if ($test->status === 'approved')
                        <p class="mt-2 rounded-lg bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700">
                            Pengujian disetujui. Silakan antarkan sampel Anda ke laboratorium.
                        </p>
                    @endif

                    @if ($test->status === 'done' && ! $test->is_paid)
                        <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                            Pengujian selesai. Silakan selesaikan pembayaran untuk mengunduh hasil.
                        </p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-4">
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <span class="text-slate-500">Diajukan {{ $test->created_at->diffForHumans() }}</span>
                            @if ($test->result)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                    Hasil: {{ $test->result }}
                                </span>
                            @endif
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $test->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $test->payment_status_label }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (in_array($test->status, ['pending', 'approved']))
                                <form action="{{ route('sample-tests.cancel', $test) }}" method="POST"
                                      data-confirm="Batalkan pengujian {{ $test->code }}?" data-confirm-accept="Ya, Batalkan">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                                        Batalkan
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('sample-tests.show', $test) }}"
                               class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Belum ada pengujian sampel.</p>
                    <a href="{{ route('sample-tests.catalog') }}"
                       class="mt-4 inline-block rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Ajukan Pengujian Pertama
                    </a>
                </div>
            @endforelse
        </div>

        @if ($tests->hasPages())
            <div class="mt-8">
                {{ $tests->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
