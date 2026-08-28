@extends('layouts.admin')

@section('title', 'Download Dokumen - MarketLabs')

@section('page', 'Download Dokumen')

@section('content')

<div class="max-w-4xl">
    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-emerald-500 to-teal-500 p-6 sm:p-8 text-white shadow-lg shadow-emerald-200/50">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-white/5"></div>
        <div class="relative z-10">
            <h2 class="text-2xl font-extrabold sm:text-3xl">Download Dokumen</h2>
            <p class="mt-2 max-w-xl text-sm text-white/80 sm:text-base">Unduh semua dokumen yang di-upload berdasarkan fitur dan rentang tanggal dalam format ZIP.</p>
        </div>
    </div>

    {{-- Form Filter --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900">Filter Dokumen</h3>
        <p class="mt-1 text-sm text-slate-500">Pilih fitur dan rentang tanggal untuk melihat dokumen yang tersedia.</p>

        <form id="filterForm" class="mt-5 grid gap-5 sm:grid-cols-3">
            @csrf
            <div>
                <label for="feature" class="block text-sm font-semibold text-slate-700">Fitur</label>
                <select id="feature" name="feature" required
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                    <option value="">-- Pilih Fitur --</option>
                    <option value="borrowing">Peminjaman Alat</option>
                    <option value="research">Proposal Riset</option>
                    <option value="sample_test">Pengujian Sampel</option>
                    <option value="health_checkup">Pemeriksaan Kesehatan</option>
                    <option value="event">Event</option>
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-semibold text-slate-700">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" required
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-semibold text-slate-700">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" required
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
            </div>
        </form>

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="button" id="previewBtn"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Tampilkan Data
            </button>
            <button type="button" id="downloadBtn"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download ZIP
            </button>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div id="summarySection" class="mt-6 hidden">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Record</p>
                <p id="totalRecords" class="mt-1 text-2xl font-extrabold text-slate-900">0</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Record dengan Dokumen</p>
                <p id="totalWithDocs" class="mt-1 text-2xl font-extrabold text-emerald-600">0</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total File</p>
                <p id="totalFiles" class="mt-1 text-2xl font-extrabold text-emerald-600">0</p>
            </div>
        </div>
    </div>

    {{-- Tabel Preview --}}
    <div id="previewSection" class="mt-6 hidden">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                <h3 class="text-lg font-bold text-slate-900">Daftar Dokumen</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">No</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kode</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">File</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Detail File</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-100">
                    </tbody>
                </table>
            </div>
            <div id="emptyState" class="hidden px-6 py-12 text-center">
                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <p class="mt-2 text-sm font-semibold text-slate-700">Tidak ada dokumen ditemukan</p>
                <p class="mt-1 text-xs text-slate-500">Tidak ada dokumen pada rentang tanggal yang dipilih.</p>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div id="loadingOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="rounded-2xl bg-white p-8 shadow-xl text-center">
            <svg class="mx-auto h-12 w-12 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p id="loadingText" class="mt-4 text-sm font-semibold text-slate-700">Memuat data...</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const filterForm = document.getElementById('filterForm');
    const featureSelect = document.getElementById('feature');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const previewBtn = document.getElementById('previewBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const summarySection = document.getElementById('summarySection');
    const previewSection = document.getElementById('previewSection');
    const tableBody = document.getElementById('tableBody');
    const emptyState = document.getElementById('emptyState');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = document.getElementById('loadingText');

    let currentData = null;

    const dateLabels = {
        borrowing: 'Tanggal Pinjam',
        research: 'Tanggal Mulai',
        sample_test: 'Tanggal Dibuat',
        health_checkup: 'Tanggal Booking',
        event: 'Tanggal Dibuat',
    };

    function showLoading(text) {
        loadingText.textContent = text || 'Memuat data...';
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
    }

    function hideLoading() {
        loadingOverlay.classList.add('hidden');
        loadingOverlay.classList.remove('flex');
    }

    function formatDate(dateStr) {
        if (!dateStr || dateStr === '-') return '-';
        const parts = dateStr.split('/');
        if (parts.length === 3) {
            return parts[0] + ' ' + ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][parseInt(parts[1])] + ' ' + parts[2];
        }
        return dateStr;
    }

    function renderTable(items) {
        if (!items || items.length === 0) {
            tableBody.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');

        tableBody.innerHTML = items.map((item, i) => {
            const fileNames = item.files.map(f => {
                const parts = f.split('/');
                return parts[parts.length - 1];
            }).join(', ');

            return `
                <tr class="transition hover:bg-slate-50/80">
                    <td class="px-6 py-4 text-sm text-slate-600">${i + 1}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-emerald-600">${escapeHtml(item.code)}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${escapeHtml(item.name)}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">${formatDate(item.date)}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                            ${item.file_count} file
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500 max-w-[200px] truncate" title="${escapeHtml(fileNames)}">${escapeHtml(fileNames)}</td>
                </tr>
            `;
        }).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    async function loadPreview() {
        const feature = featureSelect.value;
        const from = dateFrom.value;
        const to = dateTo.value;

        if (!feature || !from || !to) {
            return;
        }

        showLoading('Memuat data dokumen...');

        try {
            const params = new URLSearchParams({ feature, date_from: from, date_to: to });
            const res = await fetch(`{{ route('admin.document-downloads.preview') }}?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!res.ok) throw new Error('Gagal memuat data');

            currentData = await res.json();

            document.getElementById('totalRecords').textContent = currentData.total_records.toLocaleString('id-ID');
            document.getElementById('totalWithDocs').textContent = currentData.items.length.toLocaleString('id-ID');
            document.getElementById('totalFiles').textContent = currentData.total_files.toLocaleString('id-ID');

            renderTable(currentData.items);

            summarySection.classList.remove('hidden');
            previewSection.classList.remove('hidden');

            downloadBtn.disabled = currentData.total_files === 0;
        } catch (err) {
            alert('Terjadi kesalahan saat memuat data: ' + err.message);
        } finally {
            hideLoading();
        }
    }

    previewBtn.addEventListener('click', loadPreview);

    downloadBtn.addEventListener('click', function () {
        const feature = featureSelect.value;
        const from = dateFrom.value;
        const to = dateTo.value;

        if (!feature || !from || !to) return;

        showLoading('Membuat file ZIP...');

        const params = new URLSearchParams({ feature, date_from: from, date_to: to });
        const url = `{{ route('admin.document-downloads.download') }}?${params}`;

        const a = document.createElement('a');
        a.href = url;
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        setTimeout(hideLoading, 2000);
    });

    // Set default dates
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    dateFrom.value = firstDay.toISOString().split('T')[0];
    dateTo.value = today.toISOString().split('T')[0];
})();
</script>
@endpush
