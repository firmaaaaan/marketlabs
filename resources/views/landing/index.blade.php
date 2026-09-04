@extends('layouts.app')

@section('title', 'MarketLabs - Sewa Alat & Kelola Riset Laboratorium dalam Satu Platform')

@section('content')

{{-- ===== HERO ===== --}}
<section id="beranda" class="relative overflow-hidden bg-gradient-to-br from-emerald-900 via-emerald-800 to-green-900 pb-20 pt-28 lg:pb-28 lg:pt-36">
    {{-- Gambar latar --}}
    <img src="{{ asset('images/hero-lab.jpg') }}" alt="" aria-hidden="true"
         class="absolute inset-0 h-full w-full object-cover object-center opacity-10">
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/95 via-emerald-800/90 to-emerald-900/95"></div>

    {{-- Subtle mesh --}}
    <div class="pointer-events-none absolute inset-0 opacity-[0.04]"
         style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 32px 32px;"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            {{-- Kiri: Teks --}}
            <div class="reveal">
                <h1 class="mt-6 text-4xl font-bold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Sewa Alat &amp; Kelola Riset
                    <span class="block text-emerald-300">Laboratorium</span>
                    dalam Satu Platform
                </h1>

                <p class="mt-5 max-w-lg text-lg leading-relaxed text-emerald-100/80">
                    Peminjaman alat, pengujian sampel, hingga permohonan riset &amp; penelitian —
                    semuanya digital, transparan, dan selesai lebih cepat.
                </p>

                <form action="{{ route('tools.index') }}" method="GET" class="mt-8 flex max-w-md flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <svg class="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-emerald-400/60" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari alat laboratorium..."
                               class="w-full rounded-xl border border-white/15 bg-white/10 py-3.5 pr-4 pl-12 text-sm text-white placeholder-emerald-200/50 backdrop-blur-sm transition focus:border-emerald-400/50 focus:bg-white/15 focus:outline-none focus:ring-2 focus:ring-emerald-400/20">
                    </div>
                    <button type="submit"
                            class="rounded-xl bg-emerald-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-400 hover:shadow-emerald-400/30">
                        Cari Alat
                    </button>
                </form>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="{{ route('tools.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-emerald-900 shadow-lg shadow-black/10 transition hover:bg-emerald-50 hover:shadow-xl">
                        Jelajahi Katalog Alat
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <a href="{{ route('sample-tests.catalog') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/10">
                        Layanan Pengujian
                    </a>
                </div>
            </div>

            {{-- Kanan: Stats Cards --}}
            <div class="reveal hidden lg:block">
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20">
                            <svg class="h-6 w-6 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                            </svg>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-white">150+</p>
                        <p class="mt-1 text-sm text-emerald-200/70">Alat Laboratorium</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20">
                            <svg class="h-6 w-6 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-white">50+</p>
                        <p class="mt-1 text-sm text-emerald-200/70">Parameter Pengujian</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20">
                            <svg class="h-6 w-6 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-white">500+</p>
                        <p class="mt-1 text-sm text-emerald-200/70">Pengguna Aktif</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20">
                            <svg class="h-6 w-6 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-white">98%</p>
                        <p class="mt-1 text-sm text-emerald-200/70">Tingkat Kepuasan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== TRUST BAR ===== --}}
<section class="relative z-10 -mt-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="reveal grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-stone-200 bg-stone-200 shadow-xl sm:grid-cols-4">
            @foreach ([
                ['icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Harga Transparan', 'text' => 'Bench fee & sewa alat dihitung otomatis'],
                ['icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'title' => 'Invoice Resmi', 'text' => 'Tagihan lengkap & bisa diunduh'],
                ['icon' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z', 'title' => 'Proses Digital', 'text' => 'Ajukan & pantau status real-time'],
                ['icon' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z', 'title' => 'Dukungan Admin', 'text' => 'Laboran & admin siap membantu'],
            ] as $trust)
                <div class="flex items-start gap-3 bg-white p-5 sm:p-6">
                    <span class="flex h-10 w-10 flex-none items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trust['icon'] }}" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $trust['title'] }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $trust['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== KATEGORI ===== --}}
@if ($categories->isNotEmpty())
    <section class="py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal flex flex-wrap items-end justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Kategori</span>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        Telusuri Berdasarkan Kebutuhan
                    </h2>
                </div>
                <a href="{{ route('tools.index') }}"
                   class="group inline-flex items-center gap-1.5 rounded-lg border border-stone-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                    Lihat semua
                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>

            <div class="reveal mt-8 flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                @foreach ($categories as $category)
                    <a href="{{ route('tools.index', ['category' => $category->name]) }}"
                       class="group flex flex-none items-center gap-3 rounded-xl border border-stone-200 bg-white px-5 py-4 transition hover:border-emerald-300 hover:shadow-md hover:shadow-emerald-900/5">
                        <span class="flex h-11 w-11 flex-none items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </span>
                        <div>
                            <span class="block text-sm font-semibold text-slate-900">{{ $category->name }}</span>
                            <span class="block text-xs text-slate-500">{{ $category->tools_count }} alat</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ===== ALAT POPULER ===== --}}
<section class="bg-stone-50 pb-20 lg:pb-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Katalog Alat</span>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Alat Populer untuk Dipinjam
                </h2>
                <p class="mt-3 max-w-xl text-base text-slate-600">
                    Tambahkan ke keranjang, tentukan tanggal peminjaman, dan biaya dihitung otomatis per hari pemakaian.
                </p>
            </div>
            <a href="{{ route('tools.index') }}"
               class="group inline-flex items-center gap-1.5 rounded-lg border border-stone-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                Lihat Semua
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>

        <div class="reveal mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            @forelse ($featuredTools as $tool)
                <div class="group flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:shadow-lg hover:shadow-emerald-900/5 hover:-translate-y-0.5">
                    <a href="{{ route('tools.show', $tool) }}" class="block overflow-hidden">
                        <div class="relative flex h-48 items-center justify-center bg-slate-50">
                            @if ($tool->image)
                                <img src="{{ asset('storage/' . $tool->image) }}" alt="{{ $tool->name }}"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                            @else
                                <svg class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            @endif
                            <span class="absolute top-3 left-3 rounded-lg px-2.5 py-1 text-[11px] font-semibold shadow-sm
                                {{ $tool->available_stock <= 3 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' }}">
                                {{ $tool->available_stock <= 3 ? 'Stok Terbatas' : 'Tersedia' }}
                            </span>
                        </div>
                    </a>

                    <div class="flex flex-1 flex-col p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">{{ $tool->category?->name ?? 'Alat Laboratorium' }}</p>
                        <a href="{{ route('tools.show', $tool) }}" class="mt-1 text-sm font-semibold text-slate-900 transition hover:text-emerald-600">
                            {{ $tool->name }}
                        </a>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $tool->brand }}@if ($tool->series) {{ $tool->series }}@endif · {{ $tool->code }}
                        </p>

                        <div class="mt-auto pt-3">
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="text-xl font-bold text-slate-900">{{ $tool->formatted_price }}</p>
                                    <p class="text-[10px] text-slate-500">per hari / unit</p>
                                </div>
                            </div>

                            <div class="mt-3 border-t border-stone-100 pt-3">
                                @if ($tool->available_stock > 0)
                                    <form action="{{ route('cart.add', $tool) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit"
                                                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                            Keranjang
                                        </button>
                                        <a href="{{ route('tools.show', $tool) }}"
                                           class="rounded-xl border border-stone-200 p-2.5 text-slate-400 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600"
                                           aria-label="Lihat detail {{ $tool->name }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>
                                    </form>
                                @else
                                    <span class="block w-full rounded-xl bg-slate-100 px-4 py-2.5 text-center text-xs font-semibold text-slate-400">
                                        Stok Habis
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-16 text-center">
                    <svg class="mx-auto h-14 w-14 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                    </svg>
                    <p class="mt-4 text-base font-semibold text-slate-700">Belum ada alat tersedia</p>
                    <p class="mt-1 text-sm text-slate-500">Alat akan muncul di sini setelah admin menambahkan ke katalog.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ===== PENGUJIAN POPULER ===== --}}
<section class="py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Katalog Pengujian</span>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Layanan Pengujian Populer
                </h2>
                <p class="mt-3 max-w-xl text-base text-slate-600">
                    Pilih parameter pengujian sampel, tambahkan ke keranjang, dan biaya dihitung otomatis per sampel.
                </p>
            </div>
            <a href="{{ route('sample-tests.catalog') }}"
               class="group inline-flex items-center gap-1.5 rounded-lg border border-stone-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                Lihat Semua
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>

        <div class="reveal mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            @forelse ($featuredParameters as $parameter)
                <div class="group flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:shadow-lg hover:shadow-emerald-900/5 hover:-translate-y-0.5">
                    <a href="{{ route('sample-tests.parameter', $parameter) }}" class="block overflow-hidden">
                        <div class="relative flex h-48 items-center justify-center bg-slate-50">
                            @if ($parameter->image)
                                <img src="{{ asset('storage/' . $parameter->image) }}" alt="{{ $parameter->name }}"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                            @else
                                <svg class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15" />
                                </svg>
                            @endif
                            <span class="absolute top-3 left-3 rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                Tersedia
                            </span>
                        </div>
                    </a>

                    <div class="flex flex-1 flex-col p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">{{ $parameter->unit?->name ?? 'Pengujian Sampel' }}</p>
                        <a href="{{ route('sample-tests.parameter', $parameter) }}" class="mt-1 text-sm font-semibold text-slate-900 transition hover:text-emerald-600">
                            {{ $parameter->name }}
                        </a>
                        @if ($parameter->method)
                            <p class="mt-0.5 text-xs text-slate-500">Metode: {{ $parameter->method }}</p>
                        @else
                            <p class="mt-0.5 text-xs text-slate-500">Pengujian sampel laboratorium</p>
                        @endif

                        <div class="mt-auto pt-3">
                            <div>
                                <p class="text-xl font-bold text-slate-900">{{ $parameter->formatted_rate }}</p>
                                <p class="text-[10px] text-slate-500">per {{ strtolower($parameter->unit?->name ?? 'sampel') }}</p>
                            </div>

                            <div class="mt-3 border-t border-stone-100 pt-3">
                                <form action="{{ route('test-cart.add', $parameter) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit"
                                            class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                        </svg>
                                        Keranjang
                                    </button>
                                    <a href="{{ route('sample-tests.parameter', $parameter) }}"
                                       class="rounded-xl border border-stone-200 p-2.5 text-slate-400 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600"
                                       aria-label="Lihat detail {{ $parameter->name }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-16 text-center">
                    <svg class="mx-auto h-14 w-14 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15" />
                    </svg>
                    <p class="mt-4 text-base font-semibold text-slate-700">Belum ada layanan pengujian tersedia</p>
                    <p class="mt-1 text-sm text-slate-500">Layanan akan muncul di sini setelah admin menambahkan parameter pengujian.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ===== LAYANAN ===== --}}
<section class="bg-stone-50 py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Layanan Kami</span>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Satu Platform, Tiga Layanan Utama
            </h2>
            <p class="mt-3 text-base text-slate-600">
                Semua kebutuhan laboratorium Anda — dari alat hingga analisis — tersedia dalam satu tempat.
            </p>
        </div>

        <div class="reveal mt-12 grid gap-5 md:grid-cols-3">
            <a href="{{ route('tools.index') }}" class="group relative overflow-hidden rounded-2xl border border-stone-200 bg-white p-7 transition hover:shadow-lg hover:shadow-emerald-900/5 hover:-translate-y-0.5">
                <div class="absolute -top-20 -right-20 h-40 w-40 rounded-full bg-emerald-50 transition duration-700 group-hover:scale-150"></div>
                <div class="relative">
                    <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 transition group-hover:shadow-emerald-600/30">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                        </svg>
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-slate-900">Peminjaman Alat</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Sewa alat laboratorium dengan katalog lengkap, harga transparan per hari, dan status peminjaman real-time.
                    </p>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-600 transition group-hover:gap-2.5">
                        Mulai Pinjam
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('sample-tests.catalog') }}" class="group relative overflow-hidden rounded-2xl border border-stone-200 bg-white p-7 transition hover:shadow-lg hover:shadow-emerald-900/5 hover:-translate-y-0.5">
                <div class="absolute -top-20 -right-20 h-40 w-40 rounded-full bg-emerald-50 transition duration-700 group-hover:scale-150"></div>
                <div class="relative">
                    <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 transition group-hover:shadow-emerald-600/30">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-slate-900">Pengujian Sampel</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Pilih parameter pengujian dari katalog, tambahkan ke keranjang, dan dapatkan hasil serta invoice digital.
                    </p>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-600 transition group-hover:gap-2.5">
                        Lihat Katalog Pengujian
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ auth()->check() ? route('research.index') : route('register') }}" class="group relative overflow-hidden rounded-2xl border border-stone-200 bg-white p-7 transition hover:shadow-lg hover:shadow-emerald-900/5 hover:-translate-y-0.5">
                <div class="absolute -top-20 -right-20 h-40 w-40 rounded-full bg-emerald-50 transition duration-700 group-hover:scale-150"></div>
                <div class="relative">
                    <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 transition group-hover:shadow-emerald-600/30">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-slate-900">Riset &amp; Penelitian</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Ajukan permohonan riset secara digital, lengkapi surat pendukung, dan pantau progres penelitian.
                    </p>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-600 transition group-hover:gap-2.5">
                        {{ auth()->check() ? 'Kelola Riset Saya' : 'Daftar & Mulai' }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </div>
</section>

{{-- ===== CARA KERJA ===== --}}
<section id="cara-kerja" class="py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Cara Kerja</span>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Mulai dalam 3 Langkah Mudah
            </h2>
            <p class="mt-3 text-base text-slate-600">
                Dari pendaftaran hingga selesai, seluruh proses berjalan digital dan transparan.
            </p>
        </div>

        <div class="reveal relative mt-14 grid gap-8 md:grid-cols-3">
            {{-- Connector --}}
            <div class="absolute top-12 right-[16%] left-[16%] hidden md:block">
                <div class="h-px w-full bg-gradient-to-r from-emerald-200 via-emerald-400 to-emerald-200"></div>
            </div>

            @foreach ($steps as $index => $step)
                <div class="relative text-center md:px-4">
                    <div class="relative mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-white shadow-lg shadow-emerald-900/10 ring-1 ring-slate-200 transition hover:shadow-xl hover:shadow-emerald-900/15">
                        <svg class="h-10 w-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}" />
                        </svg>
                        <span class="absolute -top-2 -right-2 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white shadow-md shadow-emerald-600/30">
                            {{ $index + 1 }}
                        </span>
                    </div>
                    <h3 class="mt-6 text-lg font-bold text-slate-900">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $step['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== TESTIMONI ===== --}}
<section class="bg-stone-50 py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Testimoni</span>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Apa Kata Pengguna Kami
            </h2>
            <p class="mt-3 text-base text-slate-600">
                Peneliti, laboran, dan mahasiswa telah terbantu oleh MarketLabs dalam pekerjaan sehari-hari.
            </p>
        </div>

        @php
            $avatarColors = ['bg-emerald-600', 'bg-emerald-600', 'bg-emerald-700'];
        @endphp

        <div class="reveal mt-10">
            @if ($testimonials->count() > 0)
                <div class="relative">
                    <button type="button" id="testi-prev" aria-label="Testimoni sebelumnya"
                            class="absolute top-1/2 -left-4 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-stone-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-30 lg:flex">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button type="button" id="testi-next" aria-label="Testimoni berikutnya"
                            class="absolute top-1/2 -right-4 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-stone-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-30 lg:flex">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>

                    <div id="testi-viewport" class="overflow-hidden">
                        <div id="testi-track" class="flex transition-transform duration-500 ease-out">
                            @foreach ($testimonials as $testimonial)
                                <div class="w-full flex-none px-3 md:w-1/2 lg:w-1/3">
                                    <figure class="flex h-full flex-col rounded-2xl border border-stone-200 bg-white p-6 transition hover:shadow-md">
                                        <div class="flex items-center gap-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="h-4 w-4 {{ $i <= $testimonial->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 24 24">
                                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                                </svg>
                                            @endfor
                                        </div>

                                        <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-slate-600">
                                            &ldquo;{{ $testimonial->quote }}&rdquo;
                                        </blockquote>

                                        <figcaption class="mt-4 flex items-center gap-3 border-t border-stone-100 pt-4">
                                            <span class="flex h-10 w-10 flex-none items-center justify-center rounded-full {{ $avatarColors[$loop->index % count($avatarColors)] }} text-xs font-bold text-white">
                                                {{ $testimonial->initials }}
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">{{ $testimonial->name }}</p>
                                                <p class="text-xs text-slate-500">{{ $testimonial->role ?? 'Pengguna MarketLabs' }}</p>
                                            </div>
                                        </figcaption>
                                    </figure>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div id="testi-dots" class="mt-8 flex items-center justify-center gap-2"></div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-16 text-center">
                    <svg class="mx-auto h-14 w-14 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                    </svg>
                    <p class="mt-4 text-base font-semibold text-slate-700">Belum ada testimoni</p>
                    <p class="mt-1 text-sm text-slate-500">Testimoni akan muncul di sini setelah ditambahkan melalui menu admin.</p>
                </div>
            @endif
        </div>

        <script>
            (function () {
                const viewport = document.getElementById('testi-viewport');
                const track = document.getElementById('testi-track');
                if (!viewport || !track) return;

                const cards = Array.from(track.children);
                const prevBtn = document.getElementById('testi-prev');
                const nextBtn = document.getElementById('testi-next');
                const dotsWrap = document.getElementById('testi-dots');
                let index = 0;

                function perView() {
                    const w = window.innerWidth;
                    if (w >= 1024) return 3;
                    if (w >= 768) return 2;
                    return 1;
                }

                function maxIndex() {
                    return Math.max(0, Math.ceil(cards.length / perView()) - 1);
                }

                function update() {
                    const max = maxIndex();
                    if (index > max) index = max;
                    track.style.transform = 'translateX(-' + (index * 100) + '%)';

                    if (prevBtn) prevBtn.disabled = index === 0;
                    if (nextBtn) nextBtn.disabled = index >= max;

                    if (dotsWrap) {
                        dotsWrap.innerHTML = '';
                        for (let i = 0; i <= max; i++) {
                            const dot = document.createElement('button');
                            dot.type = 'button';
                            dot.setAttribute('aria-label', 'Testimoni halaman ' + (i + 1));
                            dot.className = 'h-2 rounded-full transition-all duration-300 ' +
                                (i === index ? 'w-8 bg-emerald-600' : 'w-2 bg-slate-300 hover:bg-emerald-400');
                            dot.addEventListener('click', function () { index = i; update(); });
                            dotsWrap.appendChild(dot);
                        }
                    }
                }

                if (prevBtn) prevBtn.addEventListener('click', function () { index = Math.max(0, index - 1); update(); });
                if (nextBtn) nextBtn.addEventListener('click', function () { index = Math.min(maxIndex(), index + 1); update(); });

                let startX = null;
                viewport.addEventListener('touchstart', function (e) {
                    startX = e.touches[0].clientX;
                }, { passive: true });
                viewport.addEventListener('touchend', function (e) {
                    if (startX === null) return;
                    const diff = e.changedTouches[0].clientX - startX;
                    startX = null;
                    if (Math.abs(diff) < 40) return;
                    if (diff < 0) {
                        index = Math.min(maxIndex(), index + 1);
                    } else {
                        index = Math.max(0, index - 1);
                    }
                    update();
                }, { passive: true });

                window.addEventListener('resize', update);
                update();
            })();
        </script>
    </div>
</section>

{{-- ===== FITUR ===== --}}
<section id="fitur" class="py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Fitur Unggulan</span>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Kenapa Memilih MarketLabs?
            </h2>
            <p class="mt-3 text-base text-slate-600">
                Modul lengkap untuk kebutuhan laboratorium Anda dalam satu sistem yang terintegrasi dan mudah dipelajari.
            </p>
        </div>

        <div class="reveal mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($features as $feature)
                <div class="group rounded-2xl border border-stone-200 bg-white p-6 transition hover:shadow-lg hover:shadow-emerald-900/5 hover:-translate-y-0.5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $feature['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== TENTANG ===== --}}
<section id="tentang" class="bg-stone-50 py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal grid items-center gap-12 lg:grid-cols-2">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Tentang Kami</span>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Solusi Digital untuk Pengelolaan Laboratorium Modern
                </h2>
                <p class="mt-5 text-base leading-relaxed text-slate-600">
                    MarketLabs adalah sistem informasi yang dirancang untuk membantu laboratorium
                    mengelola seluruh proses secara digital — mulai dari peminjaman alat, pengujian
                    sampel, hingga permohonan riset &amp; penelitian.
                </p>
                <p class="mt-3 text-base leading-relaxed text-slate-600">
                    Dengan teknologi terkini dan antarmuka yang sederhana, MarketLabs memastikan
                    setiap staf dan peneliti dapat bekerja lebih efisien dan akurat.
                </p>

                <ul class="mt-6 space-y-3">
                    @foreach ([
                        'Estimasi biaya otomatis: bench fee, sewa alat, biaya laboran, dan denda',
                        'Invoice resmi dan status pembayaran yang transparan',
                        'Logbook penelitian digital dengan riwayat status ber-waktu WIB',
                        'Data tersimpan aman dan terpusat dengan hak akses per peran',
                    ] as $point)
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-5 w-5 flex-none text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-sm text-slate-700">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['title' => 'Misi Kami', 'text' => 'Menyediakan sistem informasi yang andal dan mudah digunakan untuk memajukan pengelolaan laboratorium di Indonesia.'],
                    ['title' => 'Visi Kami', 'text' => 'Menjadi platform sistem informasi laboratorium terdepan yang dipercaya oleh berbagai institusi.'],
                    ['title' => 'Nilai Kami', 'text' => 'Akurat, transparan, inovatif, dan berorientasi pada kepuasan pengguna.'],
                    ['title' => 'Komitmen Kami', 'text' => 'Terus berkembang mengikuti kebutuhan zaman dengan dukungan teknologi terbaru.'],
                ] as $card)
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 transition hover:shadow-md">
                        <h3 class="text-sm font-bold text-slate-900">{{ $card['title'] }}</h3>
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">{{ $card['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ===== FAQ ===== --}}
<section id="faq" class="py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">FAQ</span>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Pertanyaan yang Sering Ditanyakan
            </h2>
            <p class="mt-3 text-base text-slate-600">
                Temukan jawaban atas pertanyaan yang paling sering diajukan pengguna.
            </p>
        </div>

        <div class="reveal mt-10 grid gap-4 md:grid-cols-2">
            @forelse ($faqs as $faq)
                <details class="group rounded-2xl border border-stone-200 bg-white p-5 transition hover:shadow-md">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold text-slate-900 [&::-webkit-details-marker]:hidden">
                        <span>{{ $faq->question }}</span>
                        <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-open:rotate-180 group-open:bg-emerald-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </span>
                    </summary>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $faq->answer }}</p>
                </details>
            @empty
                <p class="col-span-2 rounded-2xl border border-stone-200 bg-white p-8 text-center text-sm text-slate-500">
                    Belum ada FAQ yang ditampilkan.
                </p>
            @endforelse
        </div>
    </div>
</section>

{{-- ===== MITRA KAMI ===== --}}
<section class="bg-stone-50 py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Mitra Kami</span>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Dipercaya oleh Berbagai Institusi
            </h2>
            <p class="mt-3 text-base text-slate-600">
                Kami berkolaborasi dengan universitas, lembaga riset, dan instansi pemerintah untuk mendukung penelitian di Indonesia.
            </p>
        </div>

        @if ($mitras->count() > 0)
            <div class="reveal relative mt-12 overflow-hidden">
                <div class="pointer-events-none absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-stone-50 to-transparent"></div>
                <div class="pointer-events-none absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-stone-50 to-transparent"></div>

                <div class="flex w-max animate-marquee items-center gap-16">
                    @foreach ($mitras->merge($mitras) as $mitra)
                        <a href="{{ $mitra->website }}" target="_blank" rel="noopener noreferrer"
                           class="flex flex-none items-center justify-center transition-all hover:scale-105">
                            @if ($mitra->logo_url)
                                <img src="{{ $mitra->logo_url }}" alt="{{ $mitra->name }}"
                                     class="h-12 w-auto object-contain grayscale opacity-60 transition-all hover:grayscale-0 hover:opacity-100 lg:h-16">
                            @else
                                <span class="text-xl font-bold text-slate-400">{{ $mitra->name }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <p class="mt-10 text-center text-sm text-slate-500">Belum ada mitra yang terdaftar.</p>
        @endif
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-emerald-700 py-20 lg:py-28">
    <div class="pointer-events-none absolute inset-0 opacity-[0.04]"
         style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.4) 1px, transparent 0); background-size: 24px 24px;"></div>

    <div class="reveal relative mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
            Siap Mengelola Laboratorium
            <br class="hidden sm:block">
            Secara Digital?
        </h2>
        <p class="mx-auto mt-5 max-w-xl text-base text-emerald-100/90">
            Daftar sekarang dan nikmati kemudahan mengajukan peminjaman alat serta permohonan riset &amp; penelitian dalam satu platform.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('register') }}"
               class="rounded-xl bg-white px-8 py-3.5 text-sm font-semibold text-emerald-700 shadow-lg transition hover:bg-emerald-50 hover:shadow-xl">
                Daftar Gratis
            </a>
            <a href="{{ route('tools.index') }}"
               class="rounded-xl border border-white/30 bg-white/10 px-8 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                Jelajahi Katalog Alat
            </a>
        </div>
    </div>
</section>

@endsection
