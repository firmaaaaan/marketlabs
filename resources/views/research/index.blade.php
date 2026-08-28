@extends('layouts.account')

@section('title', 'Riwayat Permohonan Riset - MarketLabs')

@section('account-content')

<div>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Permohonan Riset &amp; Penelitian</h1>
            <p class="mt-1 text-sm text-slate-600">Daftar permohonan riset yang telah Anda ajukan.</p>
        </div>
        <a href="{{ route('research.create') }}"
           class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            + Ajukan Permohonan Baru
        </a>
    </div>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-8 space-y-4">
            @forelse ($proposals as $proposal)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-emerald-200">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('research.show', $proposal) }}" class="text-sm font-bold text-slate-500 transition hover:text-emerald-600">
                                    {{ $proposal->code }}
                                </a>
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
                                @if ($proposal->bench_fee !== null || $proposal->laboran_fee !== null)
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $proposal->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $proposal->payment_status_label }}
                                    </span>
                                @endif
                                @if ($proposal->customer_type)
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ $proposal->customer_type_label }}
                                    </span>
                                @endif
                            </div>
                            <h2 class="mt-2 truncate text-lg font-bold text-slate-900">
                                <a href="{{ route('research.show', $proposal) }}" class="transition hover:text-emerald-600">{{ $proposal->title }}</a>
                                @if ($proposal->user_id !== auth()->id())
                                    <span class="ml-2 inline-block rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700">Anggota</span>
                                @endif
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $proposal->field }}
                                @if ($proposal->start_date && $proposal->end_date)
                                    · {{ $proposal->start_date->translatedFormat('d M Y') }} — {{ $proposal->end_date->translatedFormat('d M Y') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <p class="text-xs text-slate-400">Diajukan {{ $proposal->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('research.show', $proposal) }}"
                                   class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                    Detail
                                </a>
                                @if ($proposal->user_id === auth()->id() && in_array($proposal->status, ['pending', 'approved']))
                                    <form action="{{ route('research.cancel', $proposal) }}" method="POST"
                                          data-confirm="Batalkan permohonan {{ $proposal->code }}?" data-confirm-accept="Ya, Batalkan">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                                            Batalkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tracker status --}}
                    @php
                        $order = ['pending', 'approved', 'ongoing', 'done'];
                        $steps = [
                            'pending' => 'Diajukan',
                            'approved' => 'Disetujui',
                            'ongoing' => 'Berlangsung',
                            'done' => 'Selesai',
                        ];
                        $icons = [
                            'pending' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                            'approved' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                            'ongoing' => 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z',
                            'done' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
                        ];
                        $reached = array_search($proposal->status, $order);
                        if ($reached === false) {
                            $reached = 0;
                        }
                        $failed = in_array($proposal->status, ['rejected', 'cancelled']);
                        $times = [
                            'pending' => $proposal->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'approved' => $proposal->approved_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'ongoing' => $proposal->ongoing_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'done' => $proposal->done_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                        ];
                    @endphp
                    <ol class="mt-6 flex">
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
                                    <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full border-2 {{ $circle }}">
                                        @if ($state === 'failed')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$key] }}" />
                                            </svg>
                                        @endif
                                    </span>
                                    @if (! $loop->last)
                                        <span class="mx-2 h-0.5 flex-1 rounded {{ $line }}"></span>
                                    @endif
                                </div>
                                <p class="mt-2 text-xs font-semibold {{ $state === 'upcoming' ? 'text-slate-400' : 'text-slate-900' }}">{{ $steps[$key] }}</p>
                                @if ($times[$key])
                                    <p class="mt-0.5 text-[11px] text-slate-400">{{ $times[$key] }} WIB</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @if ($failed)
                        <p class="mt-2 text-xs font-semibold {{ $proposal->status === 'rejected' ? 'text-red-600' : 'text-slate-500' }}">
                            {{ $proposal->status === 'rejected' ? 'Permohonan ditolak.' : 'Permohonan dibatalkan.' }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <h2 class="text-lg font-bold text-slate-900">Belum ada permohonan riset</h2>
                    <p class="mt-1 text-sm text-slate-500">Anda belum mengajukan permohonan riset &amp; penelitian.</p>
                    <a href="{{ route('research.create') }}"
                       class="mt-6 inline-block rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                        Ajukan Sekarang
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $proposals->links() }}
        </div>
    </div>
</div>

@endsection
