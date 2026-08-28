<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $borrowing->invoice_number }} - {{ \App\Models\Setting::get('invoice_company_name', 'MarketLabs') }}</title>
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
        <a href="{{ route('borrowings.show', $borrowing) }}" class="text-sm font-semibold text-slate-600 transition hover:text-emerald-600">
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
                <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $borrowing->invoice_number }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    Diterbitkan {{ $borrowing->created_at->format('d M Y, H:i') }}
                </p>
                <span class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-bold {{ $borrowing->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $borrowing->payment_status_label }}
                </span>
            </div>
        </div>

        {{-- Info peminjaman & peminjam --}}
        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ditagihkan kepada</p>
                <p class="mt-1 font-bold text-slate-900">{{ $borrowing->user->name }}</p>
                <p class="text-sm text-slate-600">{{ $borrowing->user->email }}</p>
                <p class="text-sm text-slate-600">NIM/NIP/NIDN/NIK: {{ $borrowing->nim_nip ?? '-' }}</p>
                <p class="text-sm text-slate-600">Instansi: {{ $borrowing->institution ?? '-' }}</p>
                <span class="mt-2 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    Peminjaman {{ $borrowing->borrower_type_label }}
                </span>
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Peminjaman</p>
                <p class="mt-1 font-bold text-slate-900">{{ $borrowing->code }}</p>
                <p class="mt-1 text-sm text-slate-600">
                    {{ $borrowing->borrow_date->translatedFormat('d M Y') }} — {{ $borrowing->return_date->translatedFormat('d M Y') }}
                </p>
                <p class="text-sm text-slate-600">{{ $borrowing->duration_days }} hari</p>
            </div>
        </div>

        {{-- Tabel item --}}
        <div class="mt-8 overflow-hidden rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alat</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Harga/Hari</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Subtotal/Hari</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($borrowing->items as $item)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-900">{{ $item->tool->name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $item->tool->code }}</td>
                            <td class="px-5 py-4 text-right text-slate-600">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-slate-900">{{ $item->quantity }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-slate-900">Rp {{ number_format($item->price_per_day * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Ringkasan biaya --}}
        <div class="mt-6 flex justify-end">
            <div class="w-full max-w-sm space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-600">Subtotal biaya</span>
                    <span class="font-semibold text-slate-900">{{ $borrowing->formatted_base_cost }}</span>
                </div>
                @if ($borrowing->discount_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Diskon ({{ $borrowing->discount }}%)</span>
                        <span class="font-semibold text-red-500">− {{ $borrowing->formatted_discount_amount }}</span>
                    </div>
                @endif
                @if ($borrowing->penalty > 0)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Denda keterlambatan/kerusakan</span>
                        <span class="font-semibold text-amber-600">+ {{ $borrowing->formatted_penalty }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-slate-200 pt-2">
                    <span class="font-bold text-slate-900">Total</span>
                    <span class="text-lg font-extrabold text-emerald-700">{{ $borrowing->formatted_total_cost }}</span>
                </div>
            </div>
        </div>

        {{-- Catatan --}}
        @if ($borrowing->purpose || $borrowing->pickup_notes || $borrowing->notes)
            <div class="mt-8 space-y-4 border-t border-slate-100 pt-6 text-sm">
                @if ($borrowing->purpose)
                    <div>
                        <p class="font-bold text-slate-900">Tujuan Peminjaman</p>
                        <p class="mt-1 leading-relaxed text-slate-600">{{ $borrowing->purpose }}</p>
                    </div>
                @endif
                @if ($borrowing->pickup_notes)
                    <div>
                        <p class="font-bold text-slate-900">Catatan Pengambilan Alat</p>
                        <p class="mt-1 leading-relaxed text-slate-600">{{ $borrowing->pickup_notes }}</p>
                    </div>
                @endif
                @if ($borrowing->notes)
                    <div>
                        <p class="font-bold text-slate-900">Catatan Peminjam</p>
                        <p class="mt-1 leading-relaxed text-slate-600">{{ $borrowing->notes }}</p>
                    </div>
                @endif
            </div>
        @endif

        <p class="mt-10 border-t border-slate-100 pt-6 text-center text-xs text-slate-400">
            {{ $invFooter }}
        </p>
    </div>

</body>
</html>
