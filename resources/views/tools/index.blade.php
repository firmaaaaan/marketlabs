@extends('layouts.app')

@section('title', 'Katalog Alat - MarketLabs')

@section('content')

{{-- Header Katalog --}}
<section class="bg-gradient-to-br from-emerald-700 via-emerald-600 to-emerald-800 pt-32 pb-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold text-emerald-100 backdrop-blur">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            Katalog Alat
        </span>
        <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
            Pilih Alat Laboratorium
        </h1>
        <p class="mt-3 max-w-2xl text-lg leading-relaxed text-emerald-50/90">
            Jelajahi katalog alat yang tersedia untuk peminjaman. Tambahkan ke keranjang,
            lalu ajukan peminjaman dengan tanggal yang Anda butuhkan.
        </p>

        {{-- Pencarian --}}
        <form action="{{ route('tools.index') }}" method="GET" class="mt-8 max-w-2xl">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama alat, kode, atau deskripsi..."
                       class="w-full rounded-lg border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder-emerald-200 backdrop-blur focus:border-white/50 focus:outline-none focus:ring-2 focus:ring-white/30">
                <button type="submit"
                        class="flex-none rounded-lg bg-white px-6 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                    Cari
                </button>
            </div>
        </form>
    </div>
</section>

{{-- Katalog --}}
<section class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col gap-8 lg:flex-row">
            {{-- Sidebar Kategori --}}
            <aside class="w-full flex-none lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:w-64 lg:self-start lg:overflow-y-auto">
                <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    <p class="mb-3 px-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</p>
                    <nav class="space-y-1">
                        <a href="{{ route('tools.index', array_merge(request()->except('category'), ['category' => null])) }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ !request('category') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            Semua Kategori
                        </a>
                        @foreach ($categories as $category)
                            <a href="{{ route('tools.index', array_merge(request()->except('category'), ['category' => $category->name])) }}"
                               class="flex items-center justify-between gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ request('category') === $category->name ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                                <span class="flex items-center gap-3">
                                    <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    {{ $category->name }}
                                </span>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ $category->tools_count ?? '' }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            {{-- Grid alat --}}
            <div class="min-w-0 flex-1">
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($tools as $tool)
                <div class="group flex flex-col rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
                    {{-- Gambar / placeholder alat --}}
                    <a href="{{ route('tools.show', $tool) }}" class="block">
                        <div class="flex h-32 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100">
                            @if ($tool->image)
                                <img src="{{ asset('storage/' . $tool->image) }}" alt="{{ $tool->name }}"
                                     class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <svg class="h-12 w-12 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                                </svg>
                            @endif
                        </div>
                    </a>

                    <div class="mt-3 flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">{{ $tool->category?->name ?? '-' }}</p>
                            <a href="{{ route('tools.show', $tool) }}" class="transition hover:text-emerald-600">
                                <h3 class="mt-1 text-sm font-bold text-slate-900">{{ $tool->name }}</h3>
                            </a>
                            <p class="mt-0.5 text-xs text-slate-500">Kode: {{ $tool->code }}</p>
                            @if ($tool->brand)
                                <p class="mt-0.5 text-xs text-slate-500">{{ $tool->brand }}@if ($tool->series) {{ $tool->series }}@endif</p>
                            @endif
                        </div>
                    </div>

                    <p class="mt-2 line-clamp-2 flex-1 text-xs leading-relaxed text-slate-600">{{ $tool->description }}</p>

                    <div class="mt-auto border-t border-slate-100 pt-2">
                        <p class="text-base font-extrabold text-emerald-700">{{ $tool->formatted_price }}</p>
                        <p class="text-xs text-slate-500">per hari / unit</p>
                    </div>

                    <div class="mt-2 flex items-center gap-2">
                        <a href="{{ route('tools.show', $tool) }}"
                           class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Detail
                        </a>
                        @if ($tool->available_stock > 0)
                            <form action="{{ route('cart.add', $tool) }}" method="POST" class="cart-add-form flex-1">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit"
                                        class="flex w-full items-center justify-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-md shadow-emerald-600/20 transition hover:bg-emerald-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                    </svg>
                                    Keranjang
                                </button>
                            </form>
                        @else
                            <span class="flex-1 rounded-lg bg-slate-100 px-3 py-1.5 text-center text-xs font-semibold text-slate-400">
                                Stok habis
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <p class="text-lg font-semibold text-slate-700">Tidak ada alat ditemukan</p>
                    <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci pencarian atau filter kategori.</p>
                </div>
            @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-10">
                    {{ $tools->links() }}
                </div>
            </div>
        </div>
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
        btn.innerHTML = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
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
