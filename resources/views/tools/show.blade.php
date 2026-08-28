@extends('layouts.app')

@section('title', $tool->name . ' - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-slate-500">
            <a href="{{ route('tools.index') }}" class="transition hover:text-emerald-600">Katalog</a>
            <span class="mx-2">/</span>
            <span class="text-slate-700">{{ $tool->name }}</span>
        </nav>

        <div class="mt-8 grid gap-10 lg:grid-cols-2">
            {{-- Gambar alat --}}
            <div class="flex h-80 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-50 to-emerald-100 p-8">
                @if ($tool->image)
                    <img src="{{ asset('storage/' . $tool->image) }}" alt="{{ $tool->name }}"
                         class="h-full w-full rounded-2xl object-cover">
                @else
                    <svg class="h-40 w-40 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                    </svg>
                @endif
            </div>

            {{-- Info alat --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">{{ $tool->category?->name ?? '-' }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $tool->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Kode alat: {{ $tool->code }}</p>
                @if ($tool->brand)
                    <p class="mt-1 text-sm text-slate-600">
                        Merk: <span class="font-semibold text-slate-900">{{ $tool->brand }}</span>
                        @if ($tool->series)· Seri: <span class="font-semibold text-slate-900">{{ $tool->series }}</span>@endif
                    </p>
                @endif

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-emerald-50 px-4 py-1.5 text-sm font-semibold text-emerald-700">
                        {{ $tool->formatted_price }} / hari
                    </span>
                    <span class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $tool->available_stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                        {{ $tool->available_stock > 0 ? $tool->available_stock . ' unit tersedia' : 'Stok habis' }}
                    </span>
                    <span class="rounded-full bg-slate-100 px-4 py-1.5 text-sm font-medium text-slate-600">
                        Total {{ $tool->total_stock }} unit
                    </span>
                </div>

                <p class="mt-3 text-xs text-slate-500">Biaya dihitung per hari per unit: harga × jumlah × lama hari peminjaman.</p>

                <div class="mt-6 border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-bold text-slate-900">Deskripsi</h2>
                    <p class="mt-2 leading-relaxed text-slate-600">{{ $tool->description ?? 'Belum ada deskripsi untuk alat ini.' }}</p>
                </div>

                {{-- Form peminjaman --}}
                @if ($tool->available_stock > 0)
                    <form action="{{ route('cart.add', $tool) }}" method="POST" class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm cart-add-form">
                        @csrf
                        <h3 class="font-bold text-slate-900">Ajukan Peminjaman</h3>
                        <p class="mt-1 text-sm text-slate-600">Tambahkan alat ini ke keranjang peminjaman Anda.</p>

                        <div class="mt-4 flex items-center gap-4">
                            <div>
                                <label for="quantity" class="block text-sm font-semibold text-slate-700">Jumlah</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $tool->available_stock }}"
                                       class="mt-1.5 w-28 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            </div>
                            <button type="submit"
                                    class="mt-6 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                                + Tambah ke Keranjang
                            </button>
                        </div>

                        @error('quantity')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </form>
                @else
                    <div class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-6 text-sm font-medium text-red-700">
                        Alat ini sedang tidak tersedia untuk dipinjam. Silakan hubungi admin laboratorium.
                    </div>
                @endif
            </div>
        </div>

        {{-- Alat serupa --}}
        @if ($related->isNotEmpty())
            <div class="mt-16">
                <h2 class="text-xl font-bold text-slate-900">Alat Serupa</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ route('tools.show', $item) }}"
                           class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">{{ $item->category?->name ?? '-' }}</p>
                            <h3 class="mt-1 font-bold text-slate-900">{{ $item->name }}</h3>
                            @if ($item->brand)
                                <p class="mt-0.5 text-xs text-slate-500">{{ $item->brand }}@if ($item->series) {{ $item->series }}@endif</p>
                            @endif
                            <p class="mt-3 text-sm font-semibold {{ $item->available_stock > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $item->available_stock > 0 ? $item->available_stock . ' tersedia' : 'Stok habis' }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.cart-add-form').forEach(function (form) {
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        var originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menambahkan...';
        try {
            var res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (res.ok && window.CartDrawer) {
                window.CartDrawer.refresh().then(function () { window.CartDrawer.open(); });
            } else {
                form.submit();
            }
        } catch (err) { form.submit(); }
        finally { btn.disabled = false; btn.innerHTML = originalHTML; }
    });
});
</script>
@endpush
