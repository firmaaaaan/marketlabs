<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logbook Penelitian {{ $proposal->code }} - MarketLabs</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .sheet { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 py-8 print:bg-white print:py-0">

    <div class="no-print mx-auto mb-6 flex max-w-4xl items-center justify-between px-4">
        <a href="{{ $backUrl ?? route('research.logbook', $proposal) }}" class="text-sm font-semibold text-slate-600 transition hover:text-emerald-600">
            ← Kembali ke Logbook
        </a>
        <button onclick="window.print()"
                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="sheet mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">

        {{-- Kop Surat --}}
        <div class="text-center">
            <p class="text-xl font-extrabold uppercase tracking-wide text-slate-900">UPT Laboratorium Terpadu</p>
            <p class="mt-1 text-base font-bold uppercase tracking-wide text-slate-900">Universitas 'Aisyiyah Yogyakarta</p>
            <div class="mt-3 flex flex-wrap items-center justify-center gap-x-6 gap-y-1 text-xs text-slate-600">
                <span>Jl. PHPHT No. 1, Jatisrono, Wates, Kab. Kulon Progo, DI Yogyakarta</span>
            </div>
            <div class="mt-1 flex flex-wrap items-center justify-center gap-x-6 gap-y-1 text-xs text-slate-600">
                <span>Telp. (0274) 898178</span>
                <span>Email: lab.terpadu@unisa.ac.id</span>
                <span>Web: www.unisa.ac.id</span>
            </div>
            <div class="mt-4 border-b-2 border-slate-900"></div>
        </div>

        {{-- Judul Dokumen --}}
        <div class="mt-6 text-center">
            <p class="text-lg font-extrabold uppercase tracking-widest text-slate-900">Logbook Penelitian</p>
            <p class="mt-1 text-sm font-bold text-slate-500">Kode: {{ $proposal->code }}</p>
        </div>

        {{-- Informasi proposal --}}
        <div class="grid gap-4 border-b border-slate-200 py-6 text-sm sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Judul Penelitian</p>
                <p class="mt-1 font-medium text-slate-900">{{ $proposal->title }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pemohon</p>
                <p class="mt-1 font-medium text-slate-900">{{ $proposal->user->name }}</p>
                <p class="text-xs text-slate-500">{{ $proposal->institution ?? '-' }} · {{ $proposal->nim_nip ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Periode Penelitian</p>
                <p class="mt-1 font-medium text-slate-900">
                    @if ($proposal->start_date && $proposal->end_date)
                        {{ $proposal->start_date->translatedFormat('d M Y') }} — {{ $proposal->end_date->translatedFormat('d M Y') }}
                    @else
                        -
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jumlah Entri</p>
                <p class="mt-1 font-medium text-slate-900">{{ $proposal->logbooks->count() }} entri</p>
            </div>
        </div>

        {{-- Tabel entri --}}
        <table class="mt-6 w-full text-left text-sm">
            <thead>
                <tr class="border-b-2 border-slate-300 text-xs uppercase tracking-wider text-slate-500">
                    <th class="py-2 pr-3 font-semibold">No</th>
                    <th class="py-2 pr-3 font-semibold">Tanggal</th>
                    <th class="py-2 pr-3 font-semibold">Catatan Kegiatan</th>
                    <th class="py-2 font-semibold">Kendala</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($proposal->logbooks as $logbook)
                    <tr>
                        <td class="py-3 pr-3 align-top text-slate-500">{{ $loop->iteration }}</td>
                        <td class="py-3 pr-3 align-top whitespace-nowrap font-semibold text-slate-900">{{ $logbook->log_date->translatedFormat('d M Y') }}</td>
                        <td class="py-3 pr-3 align-top whitespace-pre-line text-slate-700">{{ $logbook->note }}</td>
                        <td class="py-3 align-top whitespace-pre-line text-amber-700">{{ $logbook->obstacle ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-500">Belum ada entri logbook.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Tanda tangan --}}
        <div class="mt-16 grid grid-cols-3 gap-8 text-sm">
            <div>
                <p class="text-slate-500">Pemohon</p>
                <p class="mt-16 font-semibold text-slate-900">({{ $proposal->user->name }})</p>
            </div>
            <div>
                <p class="text-slate-500">Laboran</p>
                <p class="mt-16 font-semibold text-slate-900">({{ $proposal->laboran?->name ?? '....................' }})</p>
            </div>
            <div>
                <p class="text-slate-500">Mengetahui,</p>
                <p class="mt-16 font-semibold text-slate-900">(....................)</p>
            </div>
        </div>

        <p class="mt-10 border-t border-slate-100 pt-6 text-center text-xs text-slate-400">
            Dokumen ini dicetak dari {{ config('app.name', 'MarketLabs') }} pada {{ now()->format('d M Y H:i') }} WIB.
        </p>
    </div>

</body>
</html>
