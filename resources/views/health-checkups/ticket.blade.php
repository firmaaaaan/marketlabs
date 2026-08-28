<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nomor Antrian {{ $checkup->queue_label }} - MarketLabs</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .ticket-sheet { box-shadow: none !important; border: none !important; }
            @page { size: 100mm auto; margin: 0; }
        }
    </style>
</head>
<body class="bg-slate-100 py-8 print:bg-white print:py-0">

    <div class="no-print mx-auto mb-6 flex max-w-xl items-center justify-between px-4">
        <a href="{{ route('health-checkups.show', $checkup) }}" class="text-sm font-semibold text-slate-600 transition hover:text-emerald-600">
            ← Kembali
        </a>
        <button onclick="window.print()"
                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            🖨 Cetak No. Antrian
        </button>
    </div>

    <div class="ticket-sheet mx-auto max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Kepala tiket --}}
        <div class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-emerald-900 px-8 py-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold text-emerald-200">{{ $checkup->code }}</p>
                    <h1 class="mt-1 text-xl font-extrabold text-white">{{ $checkup->type?->name ?? 'Pemeriksaan' }}</h1>
                    <p class="mt-1 text-xs text-emerald-100/80">{{ $checkup->booking_date->translatedFormat('l, d M Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-5xl font-extrabold text-emerald-300">{{ $checkup->queue_label }}</p>
                    <p class="text-[10px] font-medium uppercase tracking-wider text-emerald-100/80">Nomor Antrian</p>
                </div>
            </div>
        </div>

        {{-- Isi --}}
        <div class="grid gap-x-6 gap-y-4 px-8 py-6 sm:grid-cols-2">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Pasien</p>
                <p class="mt-0.5 font-bold text-slate-900">{{ $checkup->user->name }}</p>
                @if ($checkup->user->nim_nip)
                    <p class="text-xs text-slate-600">{{ $checkup->user->nim_nip }}</p>
                @endif
                @if ($checkup->user->institution)
                    <p class="text-xs text-slate-600">{{ $checkup->user->institution }}</p>
                @endif
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Jenis Pemeriksaan</p>
                <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $checkup->type?->name ?? 'Pemeriksaan' }}</p>
                <p class="text-xs text-slate-500">{{ $checkup->formatted_price }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tanggal Kedatangan</p>
                <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $checkup->booking_date->translatedFormat('d M Y') }}</p>
                @if ($schedule['open_time'] && $schedule['close_time'])
                    <p class="text-xs text-slate-500">Jam layanan {{ $schedule['open_time'] }}–{{ $schedule['close_time'] }}</p>
                @endif
            </div>
            @if ($queue && $queue['position'] !== null)
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Posisi Antrian</p>
                    <p class="mt-0.5 text-sm font-bold text-sky-700">Ke-{{ $queue['position'] }} dari {{ $queue['waiting'] }}</p>
                    @if ($queue['people_ahead'] > 0)
                        <p class="text-xs text-slate-500">Masih ada {{ $queue['people_ahead'] }} orang di depan.</p>
                    @else
                        <p class="text-xs text-slate-500">Anda urutan berikutnya.</p>
                    @endif
                </div>
            @endif
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Pemeriksa</p>
                @if ($checkup->examiner)
                    <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $checkup->examiner->name }}</p>
                @else
                    <p class="mt-0.5 text-xs text-slate-500">Akan ditentukan saat kedatangan</p>
                @endif
            </div>
            @if ($checkup->purpose)
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tujuan</p>
                    <p class="mt-0.5 text-sm text-slate-700">{{ $checkup->purpose }}</p>
                </div>
            @endif
        </div>

        {{-- Kaki tiket --}}
        <div class="border-t-2 border-dashed border-slate-200 px-8 py-4 text-center">
            <p class="text-xs font-medium text-slate-500">Simpan tiket ini dan tunjukkan saat kedatangan.</p>
            <p class="mt-0.5 text-[10px] text-slate-400">MarketLabs · Laboratorium Riset &amp; Pengujian</p>
        </div>
    </div>

</body>
</html>
