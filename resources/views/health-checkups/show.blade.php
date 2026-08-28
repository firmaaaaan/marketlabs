@extends('layouts.app')

@section('title', 'Detail Booking ' . $checkup->code . ' - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('health-checkups.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
            ← Kembali ke Daftar Booking
        </a>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Kartu utama --}}
        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-emerald-900 px-8 py-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold text-emerald-200">{{ $checkup->code }}</p>
                        <h1 class="mt-1 text-2xl font-extrabold text-white">{{ $checkup->type?->name ?? 'Pemeriksaan' }}</h1>
                        <p class="mt-1 text-sm text-emerald-100/80">{{ $checkup->booking_date->translatedFormat('l, d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-extrabold text-emerald-300">{{ $checkup->queue_label }}</p>
                        <p class="text-xs font-medium text-emerald-100/80">Nomor Antrian</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 px-8 py-8 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pasien</p>
                    <p class="mt-1 font-bold text-slate-900">{{ $checkup->user->name }}</p>
                    <p class="text-sm text-slate-600">{{ $checkup->user->institution ?? '-' }}</p>
                    <p class="text-sm text-slate-600">{{ $checkup->user->nim_nip ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</p>
                    <span class="mt-1 inline-block rounded-full px-3 py-1 text-xs font-semibold {{ match ($checkup->status) {
                        'pending' => 'bg-amber-50 text-amber-700',
                        'approved' => 'bg-sky-50 text-sky-700',
                        'done' => 'bg-emerald-50 text-emerald-700',
                        'rejected' => 'bg-red-50 text-red-600',
                        'cancelled' => 'bg-slate-100 text-slate-500',
                        default => 'bg-slate-100 text-slate-500',
                    } }}">
                        {{ $checkup->status_label }}
                    </span>
                    <p class="mt-2 text-sm text-slate-600">
                        Pembayaran:
                        <span class="font-semibold {{ $checkup->is_paid ? 'text-emerald-600' : 'text-amber-600' }}">{{ $checkup->payment_status_label }}</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tarif</p>
                    <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $checkup->formatted_price }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pemeriksa</p>
                    @if ($checkup->examiner)
                        <p class="mt-1 flex items-center gap-1.5 text-sm font-medium text-slate-900">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-[10px] font-bold text-white">
                                {{ strtoupper(substr($checkup->examiner->name, 0, 1)) }}
                            </span>
                            {{ $checkup->examiner->name }}
                        </p>
                    @else
                        <p class="mt-1 text-sm text-slate-500">Akan ditentukan (bila penugasan otomatis aktif)</p>
                    @endif
                </div>
                @if ($queue && $queue['position'] !== null)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Posisi Antrian</p>
                        <p class="mt-1 text-sm font-semibold text-sky-700">
                            Ke-{{ $queue['position'] }} dari {{ $queue['waiting'] }} yang menunggu
                        </p>
                        @if ($queue['people_ahead'] > 0)
                            <p class="mt-0.5 text-xs text-slate-500">Masih ada {{ $queue['people_ahead'] }} orang di depan Anda.</p>
                        @else
                            <p class="mt-0.5 text-xs text-slate-500">Anda urutan berikutnya untuk diperiksa.</p>
                        @endif
                        <p class="mt-1.5 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                            ⚠ Posisi antrian diperbarui setiap saat. Pantau halaman ini.
                        </p>
                    </div>
                @endif
                @if ($checkup->purpose)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tujuan</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $checkup->purpose }}</p>
                    </div>
                @endif
            </div>

            {{-- Hasil --}}
            @if ($checkup->status === 'done')
                <div class="border-t border-slate-100 px-8 py-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hasil Pemeriksaan</p>
                    <div class="mt-3 rounded-2xl bg-emerald-50 px-5 py-4">
                        <p class="text-lg font-extrabold text-emerald-700">{{ $checkup->result ?? '-' }}</p>
                        @if ($checkup->result_notes)
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $checkup->result_notes }}</p>
                        @endif
                        @if ($checkup->result_file)
                            <a href="{{ route('health-checkups.result-download', $checkup) }}" target="_blank"
                               class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 transition hover:text-emerald-800">
                                📄 Lihat File Hasil Pemeriksaan
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Aksi --}}
        <div class="mt-6 flex flex-wrap items-center gap-3">
            @if (! in_array($checkup->status, ['rejected', 'cancelled']))
                <a href="{{ route('health-checkups.ticket', $checkup) }}" target="_blank"
                   class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                    🖨 Cetak No. Antrian
                </a>
            @endif
            @if (in_array($checkup->status, ['pending', 'approved']))
                <form action="{{ route('health-checkups.cancel', $checkup) }}" method="POST"
                      data-confirm="Batalkan booking {{ $checkup->code }}?" data-confirm-accept="Ya, Batalkan">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                        Batalkan Booking
                    </button>
                </form>
            @endif
            @if ($checkup->is_paid)
                <a href="{{ route('health-checkups.invoice', $checkup) }}" target="_blank"
                   class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                    Lihat Invoice
                </a>
            @endif
            @if ($checkup->status === 'done')
                <a href="{{ route('health-checkups.certificate', $checkup) }}" target="_blank"
                   class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700">
                    Unduh Surat Hasil
                </a>
            @endif
        </div>
    </div>
</section>

@endsection
