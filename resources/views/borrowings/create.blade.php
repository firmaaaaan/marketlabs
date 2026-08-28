@extends('layouts.app')

@section('title', 'Ajukan Peminjaman - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-slate-500">
            <a href="{{ route('cart.index') }}" class="transition hover:text-emerald-600">Keranjang</a>
            <span class="mx-2">/</span>
            <span class="text-slate-700">Ajukan Peminjaman</span>
        </nav>

        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Ajukan Peminjaman</h1>
        <p class="mt-2 text-slate-600">Lengkapi detail tanggal peminjaman dan pengembalian alat.</p>

        @if (session('error'))
            <div class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="mt-8 grid gap-8 lg:grid-cols-5">
            {{-- Ringkasan item --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-bold text-slate-900">Alat yang Dipinjam</h2>
                <ul class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                    @foreach ($items as $item)
                        <li class="flex items-center justify-between gap-3 text-sm">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900">{{ $item['tool']->name }}</p>
                                <p class="text-xs text-slate-500">{{ $item['tool']->code }} · {{ $item['tool']->formatted_price }}/hari</p>
                            </div>
                            <span class="flex-none rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                {{ $item['quantity'] }} unit · Rp {{ number_format($item['subtotal_per_day'], 0, ',', '.') }}/hari
                            </span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total unit</span>
                        <span class="font-bold text-slate-900">{{ $totalItems }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total / hari</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($totalPerDay, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-2">
                        <span class="font-semibold text-slate-800">Estimasi Total</span>
                        <span id="estimasi-total" class="font-extrabold text-emerald-700">—</span>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <form action="{{ route('borrowings.store') }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3">
                @csrf

                <h2 class="text-lg font-bold text-slate-900">Detail Peminjaman</h2>

                {{-- Informasi pribadi --}}
                <div class="mt-6 rounded-xl border border-emerald-100 bg-emerald-50/40 p-5">
                    <h3 class="text-sm font-bold text-slate-900">Informasi Pribadi</h3>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="full_name" class="block text-sm font-semibold text-slate-700">Nama Lengkap <span class="font-normal text-slate-400">(otomatis)</span></label>
                            <input type="text" id="full_name" value="{{ Auth::user()->name }}" readonly
                                   class="mt-1.5 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700">Email <span class="font-normal text-slate-400">(otomatis)</span></label>
                            <input type="email" id="email" value="{{ Auth::user()->email }}" readonly
                                   class="mt-1.5 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500">
                        </div>
                        <div>
                            <label for="nim_nip" class="block text-sm font-semibold text-slate-700">NIM / NIP / NIDN / NIK <span class="text-red-500">*</span></label>
                            <input type="text" id="nim_nip" name="nim_nip"
                                   value="{{ old('nim_nip', Auth::user()->nim_nip) }}" required placeholder="Contoh: 2101234567"
                                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            @error('nim_nip')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="institution" class="block text-sm font-semibold text-slate-700">Instansi <span class="text-red-500">*</span></label>
                            <input type="text" id="institution" name="institution"
                                   value="{{ old('institution', Auth::user()->institution) }}" required placeholder="Contoh: Universitas Contoh / PT Riset Nusantara"
                                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            @error('institution')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Jenis peminjaman --}}
                <div class="mt-6">
                    <span class="block text-sm font-semibold text-slate-700">Jenis Peminjaman <span class="text-red-500">*</span></span>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                            <input type="radio" name="borrower_type" value="internal"
                                   {{ old('borrower_type', 'internal') === 'internal' ? 'checked' : '' }}
                                   class="h-4 w-4 accent-emerald-600">
                            <span>
                                <span class="block text-sm font-bold text-slate-900">Internal</span>
                                <span class="block text-xs text-slate-500">Peminjam dari dalam instansi/kampus</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                            <input type="radio" name="borrower_type" value="eksternal"
                                   {{ old('borrower_type') === 'eksternal' ? 'checked' : '' }}
                                   class="h-4 w-4 accent-emerald-600">
                            <span>
                                <span class="block text-sm font-bold text-slate-900">Eksternal</span>
                                <span class="block text-xs text-slate-500">Peminjam dari luar instansi (umum/perusahaan)</span>
                            </span>
                        </label>
                    </div>
                    @error('borrower_type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="borrow_date" class="block text-sm font-semibold text-slate-700">Tanggal Peminjaman <span class="text-red-500">*</span></label>
                        <input type="date" id="borrow_date" name="borrow_date"
                               value="{{ old('borrow_date', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}"
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        @error('borrow_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="return_date" class="block text-sm font-semibold text-slate-700">Tanggal Pengembalian <span class="text-red-500">*</span></label>
                        <input type="date" id="return_date" name="return_date"
                               value="{{ old('return_date') }}" required
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        @error('return_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label for="purpose" class="block text-sm font-semibold text-slate-700">Digunakan Untuk Apa <span class="text-red-500">*</span></label>
                    <textarea id="purpose" name="purpose" rows="3" required
                              placeholder="Contoh: praktikum mata kuliah, uji sampel penelitian, kalibrasi alat, dsb."
                              class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5">
                    <label for="notes" class="block text-sm font-semibold text-slate-700">Catatan (opsional)</label>
                    <textarea id="notes" name="notes" rows="4"
                              class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5">
                    <label for="document" class="block text-sm font-semibold text-slate-700">
                        Dokumen Pendukung <span class="font-normal text-slate-400">(opsional)</span>
                    </label>
                    <input type="file" id="document" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                           class="mt-1.5 block w-full cursor-pointer rounded-lg border border-slate-300 text-sm text-slate-600 file:mr-4 file:cursor-pointer file:rounded-l-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="mt-1.5 text-xs text-slate-400">PDF, Word, atau gambar — maksimal 5 MB. Contoh: surat permohonan resmi, proposal riset.</p>
                    @error('document')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit"
                            class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                        Ajukan Peminjaman
                    </button>
                    <a href="{{ route('cart.index') }}"
                       class="rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                        Kembali ke Keranjang
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    (function () {
        const perDay = {{ $totalPerDay }};
        const borrowDate = document.getElementById('borrow_date');
        const returnDate = document.getElementById('return_date');
        const totalEl = document.getElementById('estimasi-total');

        function formatRp(value) {
            return 'Rp ' + value.toLocaleString('id-ID');
        }

        function update() {
            if (!borrowDate.value || !returnDate.value) {
                totalEl.textContent = '—';
                return;
            }

            const start = new Date(borrowDate.value);
            const end = new Date(returnDate.value);
            const diff = Math.round((end - start) / (1000 * 60 * 60 * 24));
            const days = Math.max(1, diff);

            totalEl.textContent = formatRp(perDay * days) + ' (' + days + ' hari)';
        }

        borrowDate.addEventListener('change', update);
        returnDate.addEventListener('change', update);
        update();
    })();
</script>

@endsection
