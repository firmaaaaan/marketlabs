@extends('layouts.app')

@section('title', 'Notifikasi - MarketLabs')

@section('content')

<section class="pt-32 pb-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Notifikasi</h1>
                <p class="mt-2 text-slate-600">Seluruh pemberitahuan terkait peminjaman alat Anda.</p>
            </div>
            @if ($notifications->isNotEmpty())
                <div class="flex items-center gap-2">
                    <form action="{{ route('notifications.read') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="rounded-lg border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                            Tandai Semua Dibaca
                        </button>
                    </form>
                    <button type="button" id="bulk-delete-btn"
                            class="hidden rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
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
                    $url = $n->data['url'] ?? route('borrowings.index');
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
                    <p class="mt-1 text-sm text-slate-500">Perubahan status peminjaman Anda akan muncul di sini.</p>
                    <a href="{{ route('tools.index') }}"
                       class="mt-6 inline-block rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                        Pinjam Alat
                    </a>
                </div>
            @endforelse
        </form>

        @if ($notifications->hasPages())
            <div class="mt-10">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</section>

<div id="bulk-confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeBulkModal()"></div>
    <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <h3 class="mt-4 text-center text-lg font-bold text-slate-900">Hapus Terpilih?</h3>
        <p id="bulk-confirm-message" class="mt-2 text-center text-sm leading-relaxed text-slate-600"></p>
        <div class="mt-6 flex justify-center gap-3">
            <button type="button" onclick="closeBulkModal()"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                Batal
            </button>
            <button type="button" id="bulk-confirm-accept"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                Ya, Hapus Semua
            </button>
        </div>
    </div>
</div>

<script>
function openBulkModal(count, callback) {
    var modal = document.getElementById('bulk-confirm-modal');
    var msg = document.getElementById('bulk-confirm-message');
    var accept = document.getElementById('bulk-confirm-accept');
    msg.textContent = count + ' item akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    var handler = function () {
        accept.removeEventListener('click', handler);
        closeBulkModal();
        callback();
    };
    accept.addEventListener('click', handler);
}
function closeBulkModal() {
    var modal = document.getElementById('bulk-confirm-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}
</script>

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
