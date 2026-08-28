<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Sampel {{ $test->code }} - MarketLabs</title>
    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            margin: 5mm;
            size: A4;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            padding: 16px;
        }

        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .no-print { display: none !important; }
        }

        /* ─── Stiker 18 × 50 mm ─── */
        .stiker {
            width: 50mm;
            height: 18mm;
            border: 0.5pt solid #94a3b8;
            border-radius: 2mm;
            padding: 1.5mm 2mm;
            display: flex;
            gap: 1.5mm;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
            background: #fff;
            position: relative;
        }

        /* Kiri: teks */
        .stiker__text {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stiker__header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            line-height: 1;
        }

        .stiker__lab {
            font-size: 5pt;
            font-weight: 800;
            color: #059669;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .stiker__date {
            font-size: 4pt;
            color: #94a3b8;
            line-height: 1;
        }

        .stiker__name {
            font-size: 7pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stiker__meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            line-height: 1;
        }

        .stiker__param {
            font-size: 4.5pt;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .stiker__bentuk {
            font-size: 4pt;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .stiker__code {
            font-size: 4pt;
            font-weight: 600;
            color: #64748b;
        }

        /* Kanan: QR code */
        .stiker__barcode {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 15mm;
            height: 15mm;
            gap: 0.3mm;
        }

        .stiker__qrcode {
            width: 13mm;
            height: 13mm;
        }

        .stiker__qrcode svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .stiker__pemohon {
            font-size: 3.5pt;
            color: #94a3b8;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        /* ─── Grid stiker ─── */
        .stiker-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 3mm;
        }

        /* ─── Preview area (non-print) ─── */
        .preview-info {
            max-width: 800px;
            margin: 0 auto 12px;
        }

        .preview-info h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .preview-info p {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }

        .toolbar {
            max-width: 800px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .toolbar a {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
        }

        .toolbar a:hover { color: #059669; }

        .btn-print {
            background: #059669;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(5,150,105,0.3);
            transition: background 0.15s;
        }

        .btn-print:hover { background: #047857; }

        .sticker-sheet {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8mm;
        }

        @media print {
            .sticker-sheet {
                border: none;
                border-radius: 0;
                padding: 0;
                max-width: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    {{-- Toolbar (non-print) --}}
    <div class="no-print toolbar">
        <a href="{{ $backUrl }}">← Kembali</a>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Stiker</button>
    </div>

    <div class="no-print preview-info">
        <h1>Cetak Stiker Sampel</h1>
        <p>{{ $test->code }} · {{ $test->total_samples }} stiker · Ukuran 18 × 50 mm</p>
    </div>

    {{-- Sheet stiker --}}
    <div class="sticker-sheet">
        <div class="stiker-grid">
            @foreach ($test->items as $index => $item)
                @php
                    $date = $test->received_at ?? $test->created_at;
                    $barcodeValue = $test->code . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                @endphp
                @foreach (range(1, $item->quantity) as $copy)
                    <div class="stiker">
                        {{-- Teks kiri --}}
                        <div class="stiker__text">
                            <div class="stiker__header">
                                <span class="stiker__lab">MarketLabs</span>
                                <span class="stiker__date">{{ $date->format('d/m/y') }}</span>
                            </div>
                            <div class="stiker__name" title="{{ $item->sample_name }}">
                                {{ $item->sample_name }}
                            </div>
                            @if ($item->form_label || $item->type_label)
                                <div class="stiker__bentuk" title="{{ ($item->form_label ? 'Bentuk: ' . $item->form_label : '') . ($item->form_label && $item->type_label ? ' · ' : '') . ($item->type_label ? 'Jenis: ' . $item->type_label : '') }}">
                                    @if ($item->form_label)
                                        Bentuk: {{ $item->form_label }}
                                    @endif
                                    @if ($item->type_label)
                                        {{ $item->form_label ? '·' : '' }} Jenis: {{ $item->type_label }}
                                    @endif
                                </div>
                            @endif
                            <div class="stiker__meta">
                                <span class="stiker__param" title="{{ $item->parameter?->name ?? '' }}">
                                    {{ $item->parameter?->name ?? '-' }}
                                </span>
                            </div>
                            <div class="stiker__code">{{ $test->user->name }}</div>
                        </div>

                        {{-- QR code kanan --}}
                        <div class="stiker__barcode">
                            <div class="stiker__qrcode"><div class="qrcode-wrap" data-code="{{ $barcodeValue }}"></div></div>
                            <span class="stiker__pemohon">{{ $barcodeValue }}</span>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    @if ($test->items->isEmpty())
        <div class="sticker-sheet" style="text-align:center; padding:24px; color:#94a3b8; font-size:13px;">
            Tidak ada sampel pada pengujian ini.
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.qrcode-wrap').forEach(function (el) {
                var qr = qrcode(0, 'L');
                qr.addData(el.dataset.code);
                qr.make();
                el.innerHTML = qr.createSvgTag(4, 2);
            });
        });
    </script>

</body>
</html>
