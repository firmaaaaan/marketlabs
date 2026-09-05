@extends('layouts.admin')

@section('title', 'Dashboard Analitik - MarketLabs')

@section('page', 'Analitik')

@section('content')

{{-- Header + Filter --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Dashboard Analitik</h1>
        <p class="mt-1 text-sm text-slate-500">Ringkasan data dan performa layanan laboratorium.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        {{-- Filter Dropdown --}}
        <div class="relative">
            <select id="serviceFilter"
                    class="appearance-none rounded-xl border border-slate-300 bg-white py-2.5 pl-4 pr-10 text-sm font-semibold text-slate-700 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                @foreach ($services as $key => $label)
                    <option value="{{ $key }}" {{ $key === $service ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                </svg>
            </div>
        </div>

        <a id="exportExcelBtn" href="{{ route('admin.analytics.export-excel', ['service' => $service]) }}"
           class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Excel
        </a>
        <a id="exportPdfBtn" href="{{ route('admin.analytics.export-pdf', ['service' => $service]) }}"
           class="inline-flex items-center gap-2 rounded-xl border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            PDF
        </a>
    </div>
</div>

{{-- Loading Overlay --}}
<div id="loadingOverlay" class="pointer-events-none fixed inset-0 z-40 hidden bg-white/60 backdrop-blur-sm transition-opacity">
    <div class="flex h-full items-center justify-center">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-3 shadow-lg">
            <svg class="h-5 w-5 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm font-semibold text-slate-600">Memuat data...</span>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div id="summaryCards" class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <div class="animate-pulse rounded-2xl border border-slate-200 bg-white p-5">
        <div class="h-4 w-24 rounded bg-slate-200"></div>
        <div class="mt-2 h-8 w-32 rounded bg-slate-200"></div>
    </div>
    <div class="animate-pulse rounded-2xl border border-slate-200 bg-white p-5">
        <div class="h-4 w-24 rounded bg-slate-200"></div>
        <div class="mt-2 h-8 w-32 rounded bg-slate-200"></div>
    </div>
    <div class="animate-pulse rounded-2xl border border-slate-200 bg-white p-5">
        <div class="h-4 w-24 rounded bg-slate-200"></div>
        <div class="mt-2 h-8 w-32 rounded bg-slate-200"></div>
    </div>
    <div class="animate-pulse rounded-2xl border border-slate-200 bg-white p-5">
        <div class="h-4 w-24 rounded bg-slate-200"></div>
        <div class="mt-2 h-8 w-32 rounded bg-slate-200"></div>
    </div>
</div>

{{-- Charts Row 1 --}}
<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 id="revenueChartTitle" class="text-base font-bold text-slate-900">Pendapatan per Bulan</h3>
        <p id="revenueChartSubtitle" class="mt-1 text-xs text-slate-500">6 bulan terakhir</p>
        <div class="mt-4" style="height: 280px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 id="statusChartTitle" class="text-base font-bold text-slate-900">Status Transaksi</h3>
        <p id="statusChartSubtitle" class="mt-1 text-xs text-slate-500">Distribusi status</p>
        <div class="mt-4 flex items-center justify-center" style="height: 280px;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

{{-- Charts Row 2 --}}
<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div id="serviceChartCard" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-bold text-slate-900">Pendapatan per Layanan</h3>
        <p class="mt-1 text-xs text-slate-500">Total dari semua transaksi lunas</p>
        <div class="mt-4" style="height: 280px;">
            <canvas id="serviceChart"></canvas>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-bold text-slate-900">Top 5 Alat Paling Dipinjam</h3>
        <p class="mt-1 text-xs text-slate-500">Berdasarkan jumlah peminjaman</p>
        <div class="mt-4" style="height: 280px;">
            <canvas id="topToolsChart"></canvas>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const COLORS = {
        emerald: { bg: 'rgba(16, 185, 129, 0.8)', border: '#10b981' },
        sky: { bg: 'rgba(56, 189, 248, 0.8)', border: '#38bdf8' },
        amber: { bg: 'rgba(251, 191, 36, 0.8)', border: '#fbbf24' },
        violet: { bg: 'rgba(139, 92, 246, 0.8)', border: '#8b5cf6' },
        rose: { bg: 'rgba(244, 63, 94, 0.8)', border: '#f43f5e' },
        slate: { bg: 'rgba(100, 116, 139, 0.8)', border: '#64748b' },
        teal: { bg: 'rgba(20, 184, 166, 0.8)', border: '#14b8a6' },
        orange: { bg: 'rgba(249, 115, 22, 0.8)', border: '#f97316' },
        indigo: { bg: 'rgba(99, 102, 241, 0.8)', border: '#6366f1' },
        lime: { bg: 'rgba(132, 204, 22, 0.8)', border: '#84cc16' },
    };
    const colorPalette = [COLORS.emerald.bg, COLORS.sky.bg, COLORS.amber.bg, COLORS.violet.bg, COLORS.rose.bg, COLORS.teal.bg, COLORS.orange.bg, COLORS.indigo.bg, COLORS.lime.bg, COLORS.slate.bg];
    const colorBorders = [COLORS.emerald.border, COLORS.sky.border, COLORS.amber.border, COLORS.violet.border, COLORS.rose.border, COLORS.teal.border, COLORS.orange.border, COLORS.indigo.border, COLORS.lime.border, COLORS.slate.border];

    const summaryIcons = {
        revenue: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
        transactions: 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z',
        avg: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        extra: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    };
    const summaryBgs = ['from-emerald-500 to-emerald-600', 'from-sky-500 to-blue-600', 'from-amber-500 to-orange-600', 'from-indigo-500 to-violet-600'];

    // Chart instances
    let revenueChart, statusChart, serviceChart, topToolsChart;

    function formatRupiah(val) {
        return 'Rp ' + val.toLocaleString('id-ID');
    }

    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    // -- Summary Cards --
    function renderSummaryCards(summary) {
        const cards = [
            { label: summary.revenue_label, value: formatRupiah(summary.revenue), icon: summaryIcons.revenue, bg: summaryBgs[0] },
            { label: summary.transaction_label, value: summary.transactions.toLocaleString('id-ID'), icon: summaryIcons.transactions, bg: summaryBgs[1] },
            { label: 'Rata-rata/'+summary.transaction_label, value: formatRupiah(summary.avg), icon: summaryIcons.avg, bg: summaryBgs[2] },
            { label: summary.extra.split(' ').slice(1).join(' ') || 'Informasi', value: summary.extra.split(' ')[0], icon: summaryIcons.extra, bg: summaryBgs[3] },
        ];

        const container = document.getElementById('summaryCards');
        container.innerHTML = cards.map(function(c, i) {
            return '<div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">' +
                '<div class="flex items-center justify-between">' +
                '<div class="space-y-1 min-w-0">' +
                '<p class="text-sm font-medium text-slate-500">' + c.label + '</p>' +
                '<p class="text-2xl font-extrabold tracking-tight text-slate-900 truncate">' + c.value + '</p>' +
                '</div>' +
                '<div class="flex h-12 w-12 flex-none items-center justify-center rounded-xl bg-gradient-to-br ' + c.bg + ' text-white shadow-sm transition-transform duration-300 group-hover:scale-110">' +
                '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="' + c.icon + '"/></svg>' +
                '</div>' +
                '</div>' +
                '</div>';
        }).join('');
    }

    // -- Revenue Chart --
    function renderRevenueChart(data) {
        const labels = data.map(function(d) { return d.month; });
        const values = data.map(function(d) { return d.revenue; });
        if (revenueChart) revenueChart.destroy();
        revenueChart = new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: values,
                    borderColor: COLORS.emerald.border,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3, fill: true, tension: 0.4,
                    pointBackgroundColor: COLORS.emerald.border, pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#f8fafc', bodyColor: '#cbd5e1', borderColor: '#334155', borderWidth: 1, cornerRadius: 8, padding: 12, callbacks: { label: function(ctx) { return formatRupiah(ctx.parsed.y); } } } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { callback: function(v) { if (v >= 1000000) return 'Rp ' + (v / 1000000) + 'jt'; if (v >= 1000) return 'Rp ' + (v / 1000) + 'rb'; return 'Rp ' + v; } } }, x: { grid: { display: false } } }
            }
        });
    }

    // -- Status Chart --
    function renderStatusChart(data) {
        var filtered = data.filter(function(d) { return d.count > 0; });
        var labels = filtered.map(function(d) { return d.label; });
        var counts = filtered.map(function(d) { return d.count; });
        if (statusChart) statusChart.destroy();
        if (counts.length === 0) return;
        statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: counts, backgroundColor: colorPalette.slice(0, counts.length), borderColor: '#fff', borderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12, padding: 10, font: { size: 11 }, usePointStyle: true } }, tooltip: { backgroundColor: '#1e293b', titleColor: '#f8fafc', bodyColor: '#cbd5e1', borderColor: '#334155', borderWidth: 1, cornerRadius: 8, padding: 12 } } }
        });
    }

    // -- Service Revenue Chart --
    function renderServiceChart(data) {
        var labels = data.map(function(d) { return d.service; });
        var revenues = data.map(function(d) { return d.revenue; });
        if (serviceChart) serviceChart.destroy();
        serviceChart = new Chart(document.getElementById('serviceChart'), {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Pendapatan (Rp)', data: revenues, backgroundColor: colorPalette.slice(0, 4), borderColor: colorBorders.slice(0, 4), borderWidth: 2, borderRadius: 8, borderSkipped: false }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#f8fafc', bodyColor: '#cbd5e1', borderColor: '#334155', borderWidth: 1, cornerRadius: 8, padding: 12, callbacks: { label: function(ctx) { return formatRupiah(ctx.parsed.y); } } } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { callback: function(v) { if (v >= 1000000) return 'Rp ' + (v / 1000000) + 'jt'; if (v >= 1000) return 'Rp ' + (v / 1000) + 'rb'; return 'Rp ' + v; } } }, x: { grid: { display: false } } } }
        });
    }

    // -- Top Tools Chart --
    function renderTopToolsChart(data) {
        var labels = data.map(function(d) { return d.name; });
        var quantities = data.map(function(d) { return d.quantity; });
        if (topToolsChart) topToolsChart.destroy();
        if (labels.length === 0) return;
        topToolsChart = new Chart(document.getElementById('topToolsChart'), {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Jumlah Dipinjam', data: quantities, backgroundColor: colorPalette.slice(0, labels.length), borderColor: colorBorders.slice(0, labels.length), borderWidth: 2, borderRadius: 6, borderSkipped: false }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#f8fafc', bodyColor: '#cbd5e1', borderColor: '#334155', borderWidth: 1, cornerRadius: 8, padding: 12 } }, scales: { x: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { stepSize: 1 } }, y: { grid: { display: false } } } }
        });
    }

    // -- Update Chart Titles --
    function updateChartTitles(serviceLabel, isFiltered) {
        var suffix = isFiltered ? ' - ' + serviceLabel : '';
        document.getElementById('revenueChartTitle').textContent = 'Pendapatan per Bulan';
        document.getElementById('revenueChartSubtitle').textContent = '6 bulan terakhir' + suffix;
        document.getElementById('statusChartTitle').textContent = 'Status Transaksi';
        document.getElementById('statusChartSubtitle').textContent = isFiltered ? serviceLabel : 'Distribusi status';
        document.getElementById('serviceChartCard').style.display = isFiltered ? 'none' : '';
    }

    // -- Update Export Links --
    function updateExportLinks(service) {
        var base = '{{ route("admin.analytics.export-excel") }}';
        var basePdf = '{{ route("admin.analytics.export-pdf") }}';
        var sep = service ? (base.includes('?') ? '&' : '?') + 'service=' + service : '';
        document.getElementById('exportExcelBtn').href = base + sep;
        document.getElementById('exportPdfBtn').href = basePdf + sep;
    }

    // -- Fetch & Render --
    function fetchData(service) {
        showLoading();
        var url = '{{ route("admin.analytics.data") }}' + (service ? '?service=' + service : '');

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                renderSummaryCards(data.summary);
                renderRevenueChart(data.revenuePerMonth);
                renderStatusChart(data.statusBreakdown);
                renderServiceChart(data.revenuePerService);
                renderTopToolsChart(data.topTools);
                var isFiltered = data.service !== 'all';
                updateChartTitles(data.serviceLabel, isFiltered);
                updateExportLinks(isFiltered ? data.service : '');
                hideLoading();
            })
            .catch(function() { hideLoading(); });
    }

    // -- Filter Change --
    document.getElementById('serviceFilter').addEventListener('change', function () {
        fetchData(this.value);
    });

    // -- Initial Load --
    var initialService = '{{ $service }}';
    if (initialService && initialService !== 'all') {
        fetchData(initialService);
    } else {
        // Render initial data from server (first load)
        renderSummaryCards({!! json_encode(['revenue' => 0, 'revenue_label' => 'Total Pendapatan', 'transactions' => 0, 'transaction_label' => 'Transaksi', 'avg' => 0, 'extra' => 'Memuat...']) !!});
        fetchData('all');
    }
});
</script>
@endpush
