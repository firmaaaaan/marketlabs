<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat - {{ $registration->event->title }} - MarketLabs</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .cert-sheet { box-shadow: none !important; border: none !important; }
            .cert-side-back { break-before: page; }
        }
    </style>
</head>
<body class="bg-slate-100 py-8 print:bg-white print:py-0">

    <div class="no-print mx-auto mb-6 flex max-w-4xl flex-wrap items-center justify-between gap-3 px-4">
        <a href="{{ route('events.my') }}" class="text-sm font-semibold text-slate-600 transition hover:text-emerald-600">
            ← Kembali ke Event Saya
        </a>
        <div class="flex flex-wrap items-center gap-2">
            @if ($registration->has_certificate_back)
                <a href="{{ route('events.certificate.download', ['registration' => $registration, 'back' => 1]) }}"
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                    Unduh Belakang
                </a>
            @endif
            <a href="{{ route('events.certificate.download', $registration) }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                Unduh PNG
            </a>
            <button onclick="window.print()"
                    class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div class="cert-sheet mx-auto max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:max-w-full">
        <img src="{{ \Illuminate\Support\Facades\Storage::url($registration->certificate_path) }}" alt="Sertifikat"
             class="h-auto w-full">
    </div>

    @if ($registration->has_certificate_back)
        <div class="cert-sheet cert-side-back mx-auto mt-6 max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:mt-0 print:max-w-full">
            <img src="{{ \Illuminate\Support\Facades\Storage::url($registration->certificate_back_path) }}" alt="Sertifikat Belakang"
                 class="h-auto w-full">
        </div>
    @endif



</body>
</html>