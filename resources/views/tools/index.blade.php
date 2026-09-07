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
            {{-- Sidebar Filter --}}
            <aside class="w-full flex-none lg:sticky lg:top-24 lg:h-[calc(100vh-7rem)] lg:w-64 lg:self-start lg:overflow-y-auto lg:rounded-2xl">
                <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    {{-- Filter Tipe --}}
                    <p class="mb-3 px-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Tipe Alat</p>
                    <nav class="space-y-1">
                        <a href="{{ route('tools.index', array_merge(request()->except('type'), ['type' => null])) }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ !request('type') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            Semua
                        </a>
                        <a href="{{ route('tools.index', array_merge(request()->except('type'), ['type' => 'kesehatan'])) }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ request('type') === 'kesehatan' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                            </svg>
                            Kesehatan
                        </a>
                        <a href="{{ route('tools.index', array_merge(request()->except('type'), ['type' => 'non-kesehatan'])) }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ request('type') === 'non-kesehatan' ? 'bg-amber-50 text-amber-700' : 'text-slate-700 hover:bg-slate-50 hover:text-amber-700' }}">
                            <svg class="h-5 w-5 flex-none text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                            </svg>
                            Non-Kesehatan
                        </a>
                    </nav>

                    {{-- Kategori --}}
                    <p class="mb-3 mt-6 px-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</p>
                    <nav class="space-y-1">
                        <a href="{{ route('tools.index', array_merge(request()->except('category'), ['category' => null])) }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ !request('category') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            Semua Kategori
                        </a>
                        @foreach ($categories as $category)
                            @php
                                $catType = $category->tools->first()?->type ?? 'kesehatan';
                                $isActive = request('category') === $category->name;
                                $activeBg = $catType === 'non-kesehatan' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700';
                            @endphp
                            <a href="{{ route('tools.index', array_merge(request()->except('category'), ['category' => $category->name])) }}"
                               class="flex items-center justify-between gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $isActive ? $activeBg : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                                <span class="flex items-center gap-3">
                                    @if ($catType === 'non-kesehatan')
                                        <svg class="h-5 w-5 flex-none text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 flex-none text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                        </svg>
                                    @endif
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
                <div class="group flex flex-col rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    {{-- Gambar / placeholder alat --}}
                    <a href="{{ route('tools.show', $tool) }}" class="block">
                        <div class="relative flex h-32 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100">
                            @if ($tool->image)
                                <img src="{{ asset('storage/' . $tool->image) }}" alt="{{ $tool->name }}"
                                     class="h-full w-full object-cover">
                            @else
                                <svg class="h-12 w-12 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                                </svg>
                            @endif
                            <span class="absolute top-2 left-2 rounded-md px-2 py-0.5 text-[10px] font-bold shadow-sm
                                {{ $tool->type === 'non-kesehatan' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' }}">
                                {{ $tool->type === 'non-kesehatan' ? 'Non-Kesehatan' : 'Kesehatan' }}
                            </span>
                        </div>
                    </a>

                    <div class="mt-3 flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">{{ $tool->category?->name ?? '-' }}</p>
                            <a href="{{ route('tools.show', $tool) }}" class="">
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
                           class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">
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
                                        class="flex w-full items-center justify-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-md shadow-emerald-600/20">
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
