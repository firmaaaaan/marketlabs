@extends('layouts.admin')

@section('title', 'Notifikasi - MarketLabs')

@section('page', 'Notifikasi')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Notifikasi</h1>
        <p class="mt-1 text-sm text-slate-600">Seluruh pemberitahuan untuk Anda: peminjaman, riset, pengujian, dan pemeriksaan kesehatan.</p>
    </div>
    @if ($notifications->isNotEmpty())
        <div class="flex items-center gap-2">
            <form action="{{ route('notifications.read') }}" method="POST">
                @csrf
                <button type="submit"
                        class="rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                    Tandai Semua Dibaca
                </button>
            </form>
            <button type="button" id="bulk-delete-btn"
                    class="hidden rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                Hapus (<span id="selected-count">0</span>)
            </button>
        </div>
    @endif
</div>

@if ($notifications->isNotEmpty())
    <div class="mt-4 flex items-center gap-3 text-sm text-slate-500">
        <label class="flex cursor-pointer items-center gap-2">
            <input type="checkbox" id="select-all"
                   class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span>Pilih semua</span>
        </label>
    </div>
@endif

<form id="bulk-delete-form" action="{{ route('notifications.bulk-delete') }}" method="POST" class="mt-8 space-y-3">
    @csrf
    @method('DELETE')
    @forelse ($notifications as $n)
        @php
            $url = $n->data['url'] ?? route('admin.dashboard');
            $title = $n->data['title'] ?? 'Notifikasi';
            $message = $n->data['message'] ?? null;
            $unread = $n->read_at === null;
        @endphp
        <label class="flex items-start gap-4 rounded-2xl border p-5 shadow-sm transition hover:border-emerald-200 hover:shadow cursor-pointer {{ $unread ? 'border-emerald-200 bg-emerald-50/50' : 'border-slate-200 bg-white' }}">
            <input type="checkbox" name="ids[]" value="{{ $n->id }}"
                   class="notif-checkbox mt-1.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="mt-0.5 h-2.5 w-2.5 flex-none rounded-full {{ $unread ? 'bg-emerald-500' : 'bg-slate-200' }}"></span>
            <span class="min-w-0 flex-1">
                <span class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-sm font-bold {{ $unread ? 'text-slate-900' : 'text-slate-600' }}">{{ $title }}</span>
                    <span class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                </span>
                @if ($message)
                    <span class="mt-1 block text-sm leading-relaxed text-slate-600">{{ $message }}</span>
                @endif
                @if ($unread)
                    <span class="mt-2 inline-block rounded-full bg-emerald-600 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                        Belum dibaca
                    </span>
                @endif
            </span>
            <a href="{{ $url }}" class="mt-1">
                <svg class="h-4 w-4 flex-none text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </label>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center">
            <h2 class="text-lg font-bold text-slate-900">Belum ada notifikasi</h2>
            <p class="mt-1 text-sm text-slate-500">Notifikasi peminjaman, riset, pengujian, dan pemeriksaan akan muncul di sini.</p>
        </div>
    @endforelse
</form>

@if ($notifications->hasPages())
    <div class="mt-10">
        {{ $notifications->links() }}
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('select-all');
    var checkboxes = document.querySelectorAll('.notif-checkbox');
    var bulkBtn = document.getElementById('bulk-delete-btn');
    var countSpan = document.getElementById('selected-count');

    if (!selectAll || !bulkBtn) return;

    function updateUI() {
        var checked = document.querySelectorAll('.notif-checkbox:checked').length;
        countSpan.textContent = checked;
        bulkBtn.classList.toggle('hidden', checked === 0);
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
        updateUI();
    });

    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', function () {
            selectAll.checked = document.querySelectorAll('.notif-checkbox:checked').length === checkboxes.length;
            updateUI();
        });
    });

    bulkBtn.addEventListener('click', function () {
        var checked = document.querySelectorAll('.notif-checkbox:checked');
        if (checked.length === 0) return;
        openBulkModal(checked.length, function () {
            document.getElementById('bulk-delete-form').submit();
        });
    });
});
</script>

@endsection
