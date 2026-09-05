<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 20px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #64748b; font-size: 11px; margin-bottom: 20px; }
        h2 { font-size: 14px; color: #047857; margin-top: 24px; margin-bottom: 8px; border-bottom: 2px solid #10b981; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 10px; text-align: left; font-size: 11px; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .summary-grid { display: flex; gap: 12px; margin-bottom: 16px; }
        .summary-card { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; text-align: center; }
        .summary-card .label { font-size: 10px; color: #64748b; text-transform: uppercase; }
        .summary-card .value { font-size: 16px; font-weight: bold; color: #0f172a; margin-top: 4px; }
        .footer { margin-top: 30px; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Laporan Analitik MarketLabs</h1>
    <p class="subtitle">
        UPT Laboratorium Terpadu - Universitas 'Aisyiyah Yogyakarta
        @if ($service !== 'all') | {{ $serviceLabel }} @endif
        | Dicetak: {{ $generatedAt }}
    </p>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="label">{{ $summary['revenue_label'] }}</div>
            <div class="value">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">{{ $summary['transaction_label'] }}</div>
            <div class="value">{{ number_format($summary['transactions']) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Rata-rata / {{ $summary['transaction_label'] }}</div>
            <div class="value">Rp {{ number_format($summary['avg'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Informasi</div>
            <div class="value" style="font-size: 13px;">{{ $summary['extra'] }}</div>
        </div>
    </div>

    <h2>Pendapatan per Bulan (6 Bulan Terakhir)</h2>
    <table>
        <thead>
            <tr><th>Bulan</th><th style="text-align:right">Pendapatan</th></tr>
        </thead>
        <tbody>
            @foreach ($revenuePerMonth as $item)
                <tr>
                    <td>{{ $item['month'] }}</td>
                    <td style="text-align:right">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($service === 'all')
    <h2>Pendapatan per Layanan</h2>
    <table>
        <thead>
            <tr><th>Layanan</th><th style="text-align:right">Pendapatan</th><th style="text-align:right">Jumlah Transaksi</th></tr>
        </thead>
        <tbody>
            @foreach ($revenuePerService as $item)
                <tr>
                    <td>{{ $item['service'] }}</td>
                    <td style="text-align:right">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                    <td style="text-align:right">{{ number_format($item['count']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h2>Status Transaksi</h2>
    <table>
        <thead>
            <tr><th>Status</th><th style="text-align:right">Jumlah</th></tr>
        </thead>
        <tbody>
            @foreach ($statusBreakdown as $item)
                @if ($item['count'] > 0)
                    <tr>
                        <td>{{ $item['label'] }}</td>
                        <td style="text-align:right">{{ number_format($item['count']) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <h2>Top 5 Alat Paling Dipinjam</h2>
    <table>
        <thead>
            <tr><th>Alat</th><th style="text-align:right">Jumlah Dipinjam</th></tr>
        </thead>
        <tbody>
            @forelse ($topTools as $tool)
                <tr>
                    <td>{{ $tool['name'] }}</td>
                    <td style="text-align:right">{{ number_format($tool['quantity']) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" style="text-align:center">Belum ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini digenerate secara otomatis oleh MarketLabs &mdash; {{ $generatedAt }}
    </div>
</body>
</html>
