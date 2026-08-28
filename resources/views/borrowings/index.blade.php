@extends('layouts.account')

@section('title', 'Riwayat Peminjaman - MarketLabs')

@section('account-content')

<div>            <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Riwayat Peminjaman</h1>
            <p class="mt-1 text-sm text-slate-600">Daftar peminjaman alat yang telah Anda ajukan.</p>
        </div>
            <a href="{{ route('tools.index') }}"
               class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                Pinjam Alat Baru
            </a>
        </div>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-8 space-y-4">
            @forelse ($borrowings as $borrowing)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-emerald-200">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('borrowings.show', $borrowing) }}" class="font-bold text-slate-900 transition hover:text-emerald-600">
                                    {{ $borrowing->code }}
                                </a>
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
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $borrowing->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $borrowing->payment_status_label }}
                                </span>
                                <a href="{{ route('borrowings.invoice', $borrowing) }}" target="_blank"
                                   class="rounded-full border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                    Invoice
                                </a>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ $borrowing->borrow_date->translatedFormat('d M Y') }} — {{ $borrowing->return_date->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-slate-900">{{ $borrowing->items->sum('quantity') }} unit</p>
                            <p class="text-xs text-slate-500">{{ $borrowing->items->count() }} jenis alat</p>
                        </div>
                    </div>

                    {{-- Tracker status --}}
                    @php
                        $order = ['pending', 'approved', 'borrowed', 'returned'];
                        $steps = [
                            'pending' => 'Diajukan',
                            'approved' => 'Disetujui',
                            'borrowed' => 'Dipinjam',
                            'returned' => 'Dikembalikan',
                        ];
                        $icons = [
                            'pending' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                            'approved' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                            'borrowed' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
                            'returned' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
                        ];
                        $reached = array_search($borrowing->status, $order);
                        if ($reached === false) {
                            $reached = 0;
                        }
                        $failed = in_array($borrowing->status, ['rejected', 'cancelled']);
                        $times = [
                            'pending' => $borrowing->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'approved' => $borrowing->processed_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            'borrowed' => $borrowing->borrow_date?->timezone('Asia/Jakarta')->format('d M Y'),
                            'returned' => $borrowing->returned_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
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
                                @if ($times[$key])
                                    <p class="mt-0.5 text-[11px] text-slate-400">{{ $times[$key] }} WIB</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @if ($failed)
                        <p class="mt-2 text-xs font-semibold {{ $borrowing->status === 'rejected' ? 'text-red-600' : 'text-slate-500' }}">
                            {{ $borrowing->status === 'rejected' ? 'Peminjaman ditolak.' : 'Peminjaman dibatalkan.' }}
                        </p>
                    @endif

                    @if (in_array($borrowing->status, ['pending', 'approved']))
                        <div class="mt-4 border-t border-slate-100 pt-4">
                            <form action="{{ route('borrowings.cancel', $borrowing) }}" method="POST"
                                  data-confirm="Batalkan peminjaman {{ $borrowing->code }}?" data-confirm-accept="Ya, Batalkan">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-lg border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                                    Batalkan Peminjaman
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <h2 class="text-lg font-bold text-slate-900">Belum ada peminjaman</h2>
                    <p class="mt-1 text-sm text-slate-500">Anda belum mengajukan peminjaman alat apa pun.</p>
                    <a href="{{ route('tools.index') }}"
                       class="mt-6 inline-block rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                        Mulai Pilih Alat
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $borrowings->links() }}
        </div>
    </div>
</div>

@endsection
