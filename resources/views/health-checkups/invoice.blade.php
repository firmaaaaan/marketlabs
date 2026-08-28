<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $checkup->invoice_number }} - {{ \App\Models\Setting::get('invoice_company_name', 'MarketLabs') }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .invoice-sheet { box-shadow: none !important; border: none !important; }
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

    <div class="invoice-sheet mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-12">

        {{-- Kop --}}
        @php
            $invName = \App\Models\Setting::get('invoice_company_name', 'MarketLabs');
            $invSubtitle = \App\Models\Setting::get('invoice_company_subtitle', 'Laboratorium Riset & Pengujian');
            $invTagline = \App\Models\Setting::get('invoice_company_tagline', '');
            $invAddress = \App\Models\Setting::get('invoice_company_address', 'Jln. Teknologi No. 1, Kota Sains');
            $invPhone = \App\Models\Setting::get('invoice_company_phone', '');
            $invEmail = \App\Models\Setting::get('invoice_company_email', '');
            $invWebsite = \App\Models\Setting::get('invoice_company_website', '');
            $invFooter = \App\Models\Setting::get('invoice_footer_text', 'Terima kasih telah menggunakan layanan MarketLabs. Invoice ini sah tanpa tanda tangan.');
        @endphp
        <div class="flex flex-wrap items-start justify-between gap-6 border-b border-slate-200 pb-8">
            <div>
                <p class="text-2xl font-extrabold tracking-tight text-emerald-700">{{ $invName }}</p>
                @if ($invTagline)
                    <p class="text-xs font-semibold text-slate-500">{{ $invTagline }}</p>
                @endif
                @if ($invSubtitle)
                    <p class="text-sm text-slate-500">{{ $invSubtitle }}</p>
                @endif
                @if ($invAddress)
                    <p class="mt-1 text-xs text-slate-400">{{ $invAddress }}</p>
                @endif
                @if ($invPhone || $invEmail || $invWebsite)
                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-slate-400">
                        @if ($invPhone)<span>{{ $invPhone }}</span>@endif
                        @if ($invEmail)<span>{{ $invEmail }}</span>@endif
                        @if ($invWebsite)<span>{{ $invWebsite }}</span>@endif
                    </div>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm font-bold uppercase tracking-widest text-slate-400">Invoice</p>
                <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $checkup->invoice_number }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $checkup->created_at->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        {{-- Pemohon --}}
        <div class="grid gap-6 border-b border-slate-200 py-8 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ditagihkan Kepada</p>
                <p class="mt-1 font-bold text-slate-900">{{ $checkup->user->name }}</p>
                <p class="text-sm text-slate-600">{{ $checkup->user->email }}</p>
                <p class="text-sm text-slate-600">{{ $checkup->user->institution ?? '-' }} · {{ $checkup->user->nim_nip ?? '-' }}</p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Referensi</p>
                <p class="mt-1 font-semibold text-slate-900">{{ $checkup->code }}</p>
                <p class="text-sm text-slate-600">Pemeriksaan Kesehatan</p>
                <p class="text-sm text-slate-600">{{ $checkup->booking_date->translatedFormat('d M Y') }} · Antrian {{ $checkup->queue_label }}</p>
            </div>
        </div>

        {{-- Rincian --}}
        <table class="mt-6 w-full text-left text-sm">
            <thead>
                <tr class="border-b-2 border-slate-300 text-xs uppercase tracking-wider text-slate-500">
                    <th class="py-2 pr-3 font-semibold">Layanan</th>
                    <th class="py-2 pr-3 font-semibold">Tanggal</th>
                    <th class="py-2 text-right font-semibold">Tarif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="py-3 pr-3 font-medium text-slate-900">{{ $checkup->type?->name ?? 'Pemeriksaan' }}</td>
                    <td class="py-3 pr-3 text-slate-600">{{ $checkup->booking_date->format('d/m/Y') }}</td>
                    <td class="py-3 text-right text-slate-900">{{ $checkup->formatted_price }}</td>
                </tr>
                <tr class="bg-slate-50">
                    <td colspan="2" class="py-3 pr-3 font-semibold text-slate-700">Total Keseluruhan</td>
                    <td class="py-3 text-right text-lg font-extrabold text-emerald-700">{{ $checkup->formatted_price }}</td>
                </tr>
            </tbody>
        </table>

        <p class="mt-10 border-t border-slate-100 pt-6 text-center text-xs text-slate-400">
            {{ $invFooter }}
        </p>
    </div>

</body>
</html>
