@extends('layouts.app')

@section('title', $parameter->name . ' - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-slate-500">
            <a href="{{ route('sample-tests.catalog') }}" class="transition hover:text-emerald-600">Katalog</a>
            <span class="mx-2">/</span>
            <span class="text-slate-700">{{ $parameter->name }}</span>
        </nav>

        <div class="mt-8 grid gap-10 lg:grid-cols-2">
            {{-- Gambar layanan --}}
            <div class="flex h-80 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-50 to-emerald-100 p-8">
                @if ($parameter->image)
                    <img src="{{ asset('storage/' . $parameter->image) }}" alt="{{ $parameter->name }}"
                         class="h-full w-full rounded-2xl object-cover">
                @else
                    <svg class="h-40 w-40 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                @endif
            </div>

            {{-- Info layanan --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">{{ $parameter->unit?->name ?? '-' }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $parameter->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Metode: {{ $parameter->method ?: '-' }}</p>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-emerald-50 px-4 py-1.5 text-sm font-semibold text-emerald-700">
                        {{ $parameter->formatted_rate }} / {{ strtolower($parameter->unit?->name ?? 'sampel') }}
                    </span>
                    <span class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $parameter->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                        {{ $parameter->is_active ? 'Tersedia' : 'Tidak tersedia' }}
                    </span>
                </div>

                <p class="mt-3 text-xs text-slate-500">Biaya dihitung per sampel: tarif layanan × jumlah sampel yang diajukan.</p>

                <div class="mt-6 border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-bold text-slate-900">Deskripsi</h2>
                    <p class="mt-2 leading-relaxed text-slate-600">{{ $parameter->description ?? 'Belum ada deskripsi untuk layanan ini.' }}</p>
                </div>

                {{-- Form pengajuan --}}
                @if ($parameter->is_active)
                    <form action="{{ route('test-cart.add', $parameter) }}" method="POST" class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        @csrf
                        <h3 class="font-bold text-slate-900">Ajukan Pengujian</h3>
                        <p class="mt-1 text-sm text-slate-600">Tambahkan layanan ini ke keranjang pengujian Anda.</p>

                        <div class="mt-4 flex items-center gap-4">
                            <div>
                                <label for="quantity" class="block text-sm font-semibold text-slate-700">Jumlah Sampel</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="99"
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
                        Layanan ini sedang tidak tersedia. Silakan hubungi admin laboratorium.
                    </div>
                @endif
            </div>
        </div>

        {{-- Layanan serupa --}}
        @if ($related->isNotEmpty())
            <div class="mt-16">
                <h2 class="text-xl font-bold text-slate-900">Layanan Serupa</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ route('sample-tests.parameter', $item) }}"
                           class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">{{ $item->unit?->name ?? '-' }}</p>
                            <h3 class="mt-1 font-bold text-slate-900">{{ $item->name }}</h3>
                            @if ($item->method)
                                <p class="mt-0.5 text-xs text-slate-500">Metode: {{ $item->method }}</p>
                            @endif
                            <p class="mt-3 text-sm font-semibold {{ $item->is_active ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $item->formatted_rate }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

@endsection