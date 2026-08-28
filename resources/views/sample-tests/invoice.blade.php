<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $test->invoice_number }} - {{ \App\Models\Setting::get('invoice_company_name', 'MarketLabs') }}</title>
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
        <a href="{{ route('sample-tests.show', $test) }}" class="text-sm font-semibold text-slate-600 transition hover:text-emerald-600">
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
                <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $test->invoice_number }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $test->created_at->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        {{-- Pemohon --}}
        <div class="grid gap-6 border-b border-slate-200 py-8 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ditagihkan Kepada</p>
                <p class="mt-1 font-bold text-slate-900">{{ $test->user->name }}</p>
                <p class="text-sm text-slate-600">{{ $test->user->email }}</p>
                <p class="text-sm text-slate-600">{{ $test->user->institution ?? '-' }} · {{ $test->user->nim_nip ?? '-' }}</p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Referensi</p>
                <p class="mt-1 font-semibold text-slate-900">{{ $test->code }}</p>
                <p class="text-sm text-slate-600">Pengujian Sampel</p>
                <p class="mt-1 text-sm text-slate-600">Pengiriman: {{ $test->delivery_method_label }}</p>
            </div>
        </div>

        {{-- Layanan & sampel --}}
        @forelse ($test->items->groupBy('parameter_id') as $parameterId => $items)
            @php
                $parameter = $items->first()->parameter;
                $serviceSubtotal = $items->sum(fn ($i) => $i->subtotal);
            @endphp
            <div class="mt-6">
                <div class="rounded-xl bg-slate-50 px-5 py-4">
                    <p class="text-sm font-bold text-slate-900">{{ $parameter?->name ?? 'Layanan' }}</p>
                    <p class="text-sm text-slate-600">
                        @if ($parameter?->method)
                            Metode: {{ $parameter->method }} ·
                        @endif
                        {{ $parameter?->unit?->name ?? '-' }} · {{ $parameter?->formatted_rate ?? '' }}
                    </p>
                </div>

                <table class="mt-3 w-full text-left text-sm">
                    <thead>
                        <tr class="border-b-2 border-slate-300 text-xs uppercase tracking-wider text-slate-500">
                            <th class="py-2 pr-3 font-semibold">Sampel</th>
                            <th class="py-2 pr-3 font-semibold">Bentuk / Jenis</th>
                            <th class="py-2 pr-3 font-semibold">Jumlah</th>
                            <th class="py-2 text-right font-semibold">Tarif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($items as $item)
                            <tr>
                                <td class="py-3 pr-3 font-medium text-slate-900">{{ $item->sample_name }}</td>
                                <td class="py-3 pr-3 text-slate-600">
                                    {{ $item->form_label ?: '-' }}{{ $item->type_label ? ' / ' . $item->type_label : '' }}
                                </td>
                                <td class="py-3 pr-3 text-slate-600">{{ $item->quantity }}</td>
                                <td class="py-3 text-right text-slate-900">{{ $item->formatted_subtotal }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-500">Tidak ada sampel.</td>
                            </tr>
                        @endforelse
                        <tr class="bg-slate-50">
                            <td colspan="3" class="py-3 pr-3 font-semibold text-slate-700">Subtotal {{ $parameter?->name ?? 'Layanan' }}</td>
                            <td class="py-3 text-right font-bold text-slate-900">Rp {{ number_format($serviceSubtotal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @empty
            <p class="mt-6 text-center text-sm text-slate-500">Tidak ada item.</p>
        @endforelse

        {{-- Total --}}
        <div class="mt-6 flex justify-end">
            <div class="w-full max-w-xs space-y-2 text-sm">
                <div class="flex justify-between border-t border-slate-200 pt-2">
                    <span class="font-bold text-slate-900">Total Keseluruhan</span>
                    <span class="text-lg font-extrabold text-emerald-700">{{ $test->formatted_total_cost }}</span>
                </div>
            </div>
        </div>

        <p class="mt-10 border-t border-slate-100 pt-6 text-center text-xs text-slate-400">
            {{ $invFooter }}
        </p>
    </div>

</body>
</html>
