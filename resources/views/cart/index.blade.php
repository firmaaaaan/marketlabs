@extends('layouts.app')

@section('title', 'Keranjang Saya - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Keranjang Saya</h1>
        <p class="mt-2 text-slate-600">Alat untuk peminjaman dan layanan pengujian sampel dalam satu keranjang.</p>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if (empty($items) && empty($testItems))
            <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center">
                <svg class="mx-auto h-16 w-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Keranjang Anda kosong</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih alat untuk dipinjam atau layanan pengujian sampel dari katalog.</p>
                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('tools.index') }}"
                       class="inline-block rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                        Lihat Katalog Alat
                    </a>
                    <a href="{{ route('sample-tests.catalog') }}"
                       class="inline-block rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                        Lihat Katalog Pengujian
                    </a>
                </div>
            </div>
        @else
            <div class="mt-10 grid gap-8 lg:grid-cols-3">
                {{-- Daftar item --}}
                <div class="space-y-8 lg:col-span-2">
                    {{-- Alat --}}
                    @if (! empty($items))
                        <div>
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-slate-900">Alat untuk Peminjaman</h2>
                                <form action="{{ route('cart.clear') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-semibold text-red-500 transition hover:text-red-600">
                                        Kosongkan Alat
                                    </button>
                                </form>
                            </div>
                            <div class="mt-4 space-y-4">
                                @foreach ($items as $item)
                                    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center">
                                        <div class="flex h-20 w-20 flex-none items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100">
                                            @if ($item['tool']->image)
                                                <img src="{{ asset('storage/' . $item['tool']->image) }}" alt="{{ $item['tool']->name }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <svg class="h-10 w-10 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                                                </svg>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('tools.show', $item['tool']) }}" class="font-bold text-slate-900 transition hover:text-emerald-600">
                                                {{ $item['tool']->name }}
                                            </a>
                                            <p class="text-xs text-slate-500">Kode: {{ $item['tool']->code }} · Stok tersedia: {{ $item['tool']->available_stock }}</p>
                                            <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $item['tool']->formatted_price }} / hari</p>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <div class="text-right">
                                                <p class="text-sm font-extrabold text-slate-900">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                                                <p class="text-xs text-slate-500">per hari</p>
                                            </div>
                                            {{-- Ubah jumlah --}}
                                            <form action="{{ route('cart.update', $item['tool']) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}"
                                                       min="0" max="{{ $item['max'] }}"
                                                       class="w-20 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                <button type="submit"
                                                        class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                                    Ubah
                                                </button>
                                            </form>

                                            {{-- Hapus --}}
                                            <form action="{{ route('cart.remove', $item['tool']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                                                        title="Hapus dari keranjang">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Layanan pengujian --}}
                    @if (! empty($testItems))
                        <div>
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-slate-900">Layanan Pengujian Sampel</h2>
                                <form action="{{ route('test-cart.clear') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-semibold text-red-500 transition hover:text-red-600">
                                        Kosongkan Pengujian
                                    </button>
                                </form>
                            </div>
                            <div class="mt-4 space-y-4">
                                @foreach ($testItems as $testItem)
                                    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center">
                                        <div class="flex h-20 w-20 flex-none items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100">
                                            @if ($testItem['parameter']->image)
                                                <img src="{{ asset('storage/' . $testItem['parameter']->image) }}" alt="{{ $testItem['parameter']->name }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <svg class="h-10 w-10 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                                </svg>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-slate-900">{{ $testItem['parameter']->name }}</p>
                                            <p class="text-xs text-slate-500">
                                                Satuan: {{ $testItem['parameter']->unit->name ?? '-' }}
                                                @if ($testItem['parameter']->method)
                                                    · Metode: {{ $testItem['parameter']->method }}
                                                @endif
                                            </p>
                                            <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $testItem['parameter']->formatted_rate }}</p>
                                        </div>

                                        <div class="flex flex-none items-center gap-4">
                                            <p class="text-sm font-extrabold text-slate-900">Rp {{ number_format($testItem['subtotal'], 0, ',', '.') }}</p>
                                            {{-- Hapus --}}
                                            <form action="{{ route('test-cart.remove', $testItem['parameter']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                                                        title="Hapus dari keranjang">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Ringkasan --}}
                <div class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm self-start lg:sticky lg:top-24">
                    <h2 class="text-lg font-bold text-slate-900">Ringkasan</h2>
                    <div class="mt-4 space-y-4 border-t border-slate-100 pt-4 text-sm">
                        @if (! empty($items))
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Peminjaman Alat</p>
                                <div class="mt-2 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Jenis alat</span>
                                        <span class="font-semibold text-slate-900">{{ count($items) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Total unit</span>
                                        <span class="font-semibold text-slate-900">{{ $totalItems }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Total / hari</span>
                                        <span class="font-semibold text-slate-900">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (! empty($testItems))
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengujian Sampel</p>
                                <div class="mt-2 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Jenis layanan</span>
                                        <span class="font-semibold text-slate-900">{{ count($testItems) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Total</span>
                                        <span class="font-semibold text-slate-900">Rp {{ number_format($testTotalCost, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-between border-t border-slate-100 pt-3">
                            <span class="font-bold text-slate-900">Total Keseluruhan</span>
                            <span class="font-extrabold text-emerald-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Biaya alat dihitung × lama hari saat checkout; biaya pengujian final dihitung dari jumlah sampel.</p>

                    @if (! empty($items))
                        <a href="{{ route('borrowings.create') }}"
                           class="mt-6 block rounded-lg bg-emerald-600 px-6 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                            Lanjutkan ke Peminjaman Alat
                        </a>
                    @endif

                    @if (! empty($testItems))
                        <a href="{{ route('sample-tests.checkout') }}"
                           class="mt-3 block rounded-lg bg-emerald-600 px-6 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                            Lanjutkan ke Pengujian Sampel
                        </a>
                    @endif

                    <a href="{{ route('tools.index') }}"
                       class="mt-3 block rounded-lg border border-slate-300 px-6 py-3 text-center text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                        Lanjut Belanja Alat
                    </a>

                    <a href="{{ route('sample-tests.catalog') }}"
                       class="mt-3 block rounded-lg border border-slate-300 px-6 py-3 text-center text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                        Lanjut Pilih Layanan Pengujian
                    </a>

                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                        @if (! empty($items))
                            <form action="{{ route('cart.clear') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-500 transition hover:text-red-600">
                                    Kosongkan Alat
                                </button>
                            </form>
                        @endif
                        @if (! empty($testItems))
                            <form action="{{ route('test-cart.clear') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-500 transition hover:text-red-600">
                                    Kosongkan Pengujian
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

@endsection
