{{-- Cart Drawer – slide-in panel dari kanan --}}
<div id="cart-drawer-backdrop"
     class="fixed inset-0 z-[9998] bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0 pointer-events-none">
</div>

<aside id="cart-drawer"
       class="fixed top-0 right-0 z-[9999] flex h-full w-full max-w-md flex-col bg-white shadow-2xl transition-transform duration-300 ease-in-out translate-x-full">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-900">
            Keranjang Saya
            <span id="cart-drawer-badge"
                  class="ml-2 inline-flex items-center justify-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700 hidden">
            </span>
        </h2>
        <button type="button" id="cart-drawer-close"
                class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                aria-label="Tutup keranjang">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Isi keranjang --}}
    <div id="cart-drawer-body" class="flex-1 overflow-y-auto px-6 py-4">
        {{-- Kosong --}}
        <div id="cart-drawer-empty" class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="h-16 w-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="mt-4 text-sm font-semibold text-slate-700">Keranjang Anda kosong</p>
            <p class="mt-1 text-xs text-slate-500">Pilih alat atau layanan pengujian dari katalog.</p>
        </div>

        {{-- Daftar item --}}
        <div id="cart-drawer-items" class="space-y-4 hidden">
            <div id="cart-drawer-tools-section" class="hidden">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Alat Peminjaman</p>
                <div id="cart-drawer-tools" class="space-y-3"></div>
            </div>
            <div id="cart-drawer-tests-section" class="hidden">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Layanan Pengujian</p>
                <div id="cart-drawer-tests" class="space-y-3"></div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div id="cart-drawer-footer" class="border-t border-slate-200 px-6 py-4 space-y-3 hidden">
        <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700">Total</span>
            <span id="cart-drawer-total" class="text-lg font-extrabold text-emerald-700">Rp 0</span>
        </div>
        <a href="{{ route('cart.index') }}"
           class="block w-full rounded-lg bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Lihat Keranjang
        </a>
    </div>
</aside>
