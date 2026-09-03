@extends('layouts.admin')

@section('title', 'Backup & Restore - MarketLabs')

@section('page', 'Backup & Restore Database')

@section('content')

@if (session('success'))
    <div class="mb-6 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        <svg class="h-5 w-5 flex-none text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        <svg class="h-5 w-5 flex-none text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
        {{ session('error') }}
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-2">

    {{-- Card: Buat Backup --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">Buat Backup</h2>
                <p class="text-sm text-slate-500">Ekspor seluruh data database ke file JSON</p>
            </div>
        </div>

        <form action="{{ route('admin.backup.store') }}" method="POST" class="mt-5" id="backup-form">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700">Pilih Tabel</label>
                <p class="mt-1 text-xs text-slate-500">Kosongkan untuk backup semua tabel</p>
                <div id="table-checkboxes" class="mt-3 max-h-64 space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3"
                     data-url="{{ route('admin.backup.tables') }}">
                    <p class="text-xs text-slate-400">Memuat tabel...</p>
                </div>
            </div>
            <button type="submit"
                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Buat Backup Sekarang
            </button>
        </form>
    </div>

    {{-- Card: Restore --}}
    <div class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">Restore Database</h2>
                <p class="text-sm text-slate-500">Impor data dari file backup</p>
            </div>
        </div>

        <div class="mt-5 rounded-lg border border-amber-300 bg-amber-50 p-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-none text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="text-sm text-amber-800">
                    <p class="font-bold">Peringatan!</p>
                    <p class="mt-1">Restore akan <strong>menghapus semua data saat ini</strong> dan menggantinya dengan data dari file backup.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data" id="restore-form" class="mt-5">
            @csrf

            {{-- Dropzone --}}
            <div id="restore-dropzone"
                 class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-10 transition hover:border-amber-400 hover:bg-amber-50/30">
                <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-600">Seret file ke sini atau</p>
                <label for="restore-file-input"
                       class="mt-3 cursor-pointer rounded-lg bg-amber-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                    Pilih File
                </label>
                <input type="file" name="backup_file" id="restore-file-input" accept=".json" class="hidden">
                <p class="mt-2 text-xs text-slate-400">Format: .json (maks 10MB)</p>
            </div>

            {{-- File Info --}}
            <div id="restore-file-info" class="mt-4 hidden">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 flex-none text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-emerald-800" id="restore-filename">-</p>
                            <p class="mt-0.5 text-xs text-emerald-600" id="restore-file-detail">-</p>
                        </div>
                        <button type="button" onclick="clearRestoreFile()" class="rounded p-1 text-emerald-400 transition hover:bg-emerald-100 hover:text-emerald-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Checkbox --}}
            <label class="mt-4 flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="confirm" id="restore-confirm" value="1"
                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                <span class="text-sm text-slate-700">Saya mengerti dan yakin ingin melanjutkan restore</span>
            </label>

            {{-- File error --}}
            <p id="restore-file-error" class="mt-2 hidden text-xs text-red-500"></p>

            <button type="submit" id="restore-submit-btn" disabled
                    data-confirm="Restore akan menghapus semua data saat ini dan menggantinya dari file backup. Tindakan ini tidak dapat dibatalkan!" data-confirm-accept="Ya, Restore!"
                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                </svg>
                Restore Sekarang
            </button>
        </form>
    </div>
</div>

{{-- Tabel Backup --}}
<div class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-900">Riwayat Backup</h2>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($backups) }} file</span>
    </div>

    @if (count($backups) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Nama File</th>
                        <th class="px-6 py-3">Ukuran</th>
                        <th class="px-6 py-3">Tabel</th>
                        <th class="px-6 py-3">Dibuat Oleh</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($backups as $backup)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100">
                                        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-slate-900">{{ $backup['filename'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $backup['size_label'] }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm">{{ $backup['table_count'] }} tabel</span>
                            </td>
                            <td class="px-6 py-4">{{ $backup['created_by'] }}</td>
                            <td class="px-6 py-4 text-xs text-slate-500">{{ $backup['created_at'] }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.backup.download', $backup['filename']) }}"
                                       class="rounded-lg bg-emerald-50 p-1.5 text-emerald-600 transition hover:bg-emerald-100"
                                       title="Download">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.backup.destroy', $backup['filename']) }}" method="POST"
                                          data-confirm="Hapus backup {{ $backup['filename'] }}?" data-confirm-accept="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg bg-red-50 p-1.5 text-red-600 transition hover:bg-red-100"
                                                title="Hapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
            </svg>
            <p class="mt-3 text-sm font-medium text-slate-500">Belum ada backup</p>
            <p class="mt-1 text-xs text-slate-400">Buat backup pertama Anda menggunakan form di atas</p>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('table-checkboxes');
    const url = container.dataset.url;

    fetch(url)
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(tables => {
            container.innerHTML = '';
            const excluded = ['migrations', 'jobs', 'failed_jobs', 'cache', 'sessions', 'password_reset_tokens', 'sqlite_sequence'];

            Object.entries(tables).forEach(([name, count]) => {
                const isExcluded = excluded.includes(name);
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-center gap-3 rounded-lg px-3 py-2 transition ' + (isExcluded ? 'opacity-50' : 'hover:bg-slate-50');

                wrapper.innerHTML =
                    '<input type="checkbox" name="tables[]" value="' + name + '" id="tbl-' + name + '"' +
                    (isExcluded ? ' disabled' : '') +
                    ' class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">' +
                    '<label for="tbl-' + name + '" class="flex-1 cursor-pointer text-sm text-slate-700">' + name + '</label>' +
                    '<span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">' + count.toLocaleString() + ' baris</span>' +
                    (isExcluded ? '<span class="text-[10px] font-semibold text-slate-400">dikecualikan</span>' : '');

                container.appendChild(wrapper);
            });
        })
        .catch(err => {
            container.innerHTML = '<p class="text-xs text-red-500">Gagal memuat tabel: ' + err.message + '</p>';
        });

    // Restore file input
    const fileInput = document.getElementById('restore-file-input');
    const confirmCheck = document.getElementById('restore-confirm');
    const submitBtn = document.getElementById('restore-submit-btn');
    const fileError = document.getElementById('restore-file-error');

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;

        fileError.classList.add('hidden');

        if (file.type !== 'application/json' && !file.name.endsWith('.json')) {
            fileError.textContent = 'File harus berformat JSON';
            fileError.classList.remove('hidden');
            fileInput.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            fileError.textContent = 'Ukuran file maksimal 10MB';
            fileError.classList.remove('hidden');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const data = JSON.parse(e.target.result);
                const meta = data.metadata || {};
                const tableCount = Object.keys(data.tables || {}).length;
                const totalRows = Object.values(data.tables || {}).reduce((sum, t) => sum + (t.row_count || 0), 0);

                document.getElementById('restore-filename').textContent = file.name;
                document.getElementById('restore-file-detail').textContent =
                    tableCount + ' tabel | ' + totalRows.toLocaleString() + ' baris' +
                    (meta.created_by ? ' | Dibuat oleh: ' + meta.created_by : '') +
                    (meta.created_at ? ' | ' + meta.created_at.split('T')[0] : '');

                document.getElementById('restore-dropzone').classList.add('hidden');
                document.getElementById('restore-file-info').classList.remove('hidden');
            } catch (err) {
                fileError.textContent = 'File JSON tidak valid: ' + err.message;
                fileError.classList.remove('hidden');
                fileInput.value = '';
            }
        };
        reader.readAsText(file);
    });

    confirmCheck.addEventListener('change', () => {
        submitBtn.disabled = !(confirmCheck.checked && fileInput.files.length > 0);
    });
});

function clearRestoreFile() {
    document.getElementById('restore-file-input').value = '';
    document.getElementById('restore-dropzone').classList.remove('hidden');
    document.getElementById('restore-file-info').classList.add('hidden');
    document.getElementById('restore-confirm').checked = false;
    document.getElementById('restore-submit-btn').disabled = true;
}
</script>
@endpush
