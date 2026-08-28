{{-- Modal konfirmasi pengganti confirm() bawaan browser.
     Pakai: <form ... data-confirm="Pesan" data-confirm-accept="Ya, Hapus">
     Atribut data-confirm-accept opsional (teks tombol konfirmasi, default "Ya, Lanjutkan"). --}}
<div id="confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-confirm-close></div>
    <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <h3 class="mt-4 text-center text-lg font-bold text-slate-900">Konfirmasi Tindakan</h3>
        <p id="confirm-message" class="mt-2 text-center text-sm leading-relaxed text-slate-600"></p>
        <div class="mt-6 flex justify-center gap-3">
            <button type="button" data-confirm-close
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                Batal
            </button>
            <button type="button" id="confirm-accept"
                    class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('confirm-modal');
    if (!modal || modal.dataset.bound) return;
    modal.dataset.bound = '1';

    var messageEl = document.getElementById('confirm-message');
    var acceptBtn = document.getElementById('confirm-accept');
    var pendingForm = null;
    var defaultAccept = acceptBtn.textContent;

    function close() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        pendingForm = null;
        acceptBtn.textContent = defaultAccept;
    }

    document.addEventListener('submit', function (e) {
        var form = e.target && e.target.closest ? e.target.closest('form[data-confirm]') : null;
        if (!form) return;
        if (form.getAttribute('data-confirmed') === '1') return;
        e.preventDefault();
        e.stopPropagation();
        pendingForm = form;
        messageEl.textContent = form.getAttribute('data-confirm') || 'Yakin ingin melanjutkan tindakan ini?';
        acceptBtn.textContent = form.getAttribute('data-confirm-accept') || defaultAccept;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    });

    acceptBtn.addEventListener('click', function () {
        if (pendingForm) {
            pendingForm.setAttribute('data-confirmed', '1');
            pendingForm.submit();
        }
        close();
    });

    modal.querySelectorAll('[data-confirm-close]').forEach(function (el) {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
    });
})();
</script>