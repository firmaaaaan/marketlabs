<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Hasil {{ $checkup->code }} - MarketLabs</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .cert-sheet { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 py-8 print:bg-white print:py-0">

    <div class="no-print mx-auto mb-6 flex max-w-3xl items-center justify-between px-4">
        <a href="{{ route('health-checkups.show', $checkup) }}" class="text-sm font-semibold text-slate-600 transition hover:text-emerald-600">
            ← Kembali
        </a>
        <button onclick="window.print()"
                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="cert-sheet mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-12">

        {{-- Kop --}}
        <div class="flex items-center justify-between gap-6 border-b-2 border-slate-800 pb-6">
            <div>
                <p class="text-2xl font-extrabold tracking-tight text-emerald-700">MarketLabs</p>
                <p class="mt-1 text-sm text-slate-500">Laboratorium Riset &amp; Pengujian</p>
                <p class="text-xs text-slate-400">Jln. Teknologi No. 1, Kota Sains</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold uppercase tracking-widest text-slate-500">Surat Hasil</p>
                <p class="mt-1 text-lg font-extrabold text-slate-900">Pemeriksaan Kesehatan</p>
                <p class="mt-1 text-xs text-slate-500">{{ $checkup->code }} · {{ $checkup->done_at?->translatedFormat('d M Y') ?? $checkup->updated_at->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        {{-- Identitas pasien --}}
        <div class="grid gap-6 py-8 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Pasien</p>
                <p class="mt-1 font-bold text-slate-900">{{ $checkup->user->name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">NIM / NIP</p>
                <p class="mt-1 text-slate-900">{{ $checkup->user->nim_nip ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Instansi</p>
                <p class="mt-1 text-slate-900">{{ $checkup->user->institution ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Pemeriksaan</p>
                <p class="mt-1 text-slate-900">{{ $checkup->booking_date->translatedFormat('d M Y') }} · Antrian {{ $checkup->queue_label }}</p>
            </div>
        </div>

        {{-- Hasil --}}
        <div class="rounded-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-center justify-between gap-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Pemeriksaan</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ $checkup->type?->name ?? 'Pemeriksaan' }}</p>
                    @if ($checkup->purpose)
                        <p class="mt-0.5 text-sm text-slate-500">Tujuan: {{ $checkup->purpose }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hasil</p>
                    <p class="mt-1 text-2xl font-extrabold {{ in_array($checkup->result, ['Reaktif', 'Positif']) ? 'text-red-600' : 'text-emerald-700' }}">
                        {{ $checkup->result ?? '-' }}
                    </p>
                </div>
            </div>
            @if ($checkup->result_notes)
                <p class="mt-4 whitespace-pre-line border-t border-slate-100 pt-4 text-sm text-slate-600">{{ $checkup->result_notes }}</p>
            @endif
        </div>

        <p class="mt-8 text-center text-xs text-slate-400">
            Surat hasil ini diterbitkan otomatis oleh sistem MarketLabs dan sah tanpa tanda tangan.
        </p>
    </div>

</body>
</html>
