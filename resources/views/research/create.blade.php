@extends('layouts.app')

@section('title', 'Ajukan Permohonan Riset - MarketLabs')

@section('content')

    <section class="py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <a href="{{ auth()->check() ? route('research.index') : route('home') }}"
                class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
                ← Kembali
            </a>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">Ajukan Permohonan Riset &amp; Penelitian
            </h1>
            <p class="mt-2 text-slate-600">Lengkapi informasi berikut untuk mengajukan permohonan riset &amp; penelitian di
                laboratorium.</p>

            @guest
                <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6">
                    <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold text-slate-900">Login Diperlukan</h2>
                            <p class="mt-1 text-sm text-slate-600">Anda perlu masuk ke akun Anda untuk mengajukan permohonan riset &amp; penelitian. Silakan login atau daftar terlebih dahulu.</p>
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('login') }}" class="shrink-0 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">Masuk</a>
                            <a href="{{ route('register') }}" class="shrink-0 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">Daftar</a>
                        </div>
                    </div>
                </div>
            @endguest

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <p class="text-sm font-bold text-red-700">Periksa kembali isian Anda:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-8 items-start md:grid md:grid-cols-3 md:gap-6 xl:gap-8">
                @auth
                <div class="md:col-span-2">
                    <form action="{{ route('research.store') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-6">
                            @csrf
    
                            {{-- Informasi Pemohon --}}
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-6">
                                <h2 class="text-lg font-bold text-slate-900">Informasi Pemohon</h2>
                                <p class="mt-1 text-sm text-slate-500">Diambil otomatis dari akun Anda.</p>
                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700">Nama Lengkap <span
                                                class="text-red-500">*</span> <span
                                                class="text-xs font-normal text-slate-400">(otomatis)</span></label>
                                        <input type="text" value="{{ $user->name }}" readonly
                                            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700">Email <span
                                                class="text-red-500">*</span> <span
                                                class="text-xs font-normal text-slate-400">(otomatis)</span></label>
                                        <input type="text" value="{{ $user->email }}" readonly
                                            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-500">
                                    </div>
                                    <div>
<label for="nim_nip" class="block text-sm font-semibold text-slate-700">NIM / NIP /
                                                NIDN / NIK <span class="text-red-500">*</span></label>
                                        <input type="text" id="nim_nip" name="nim_nip"
                                            value="{{ old('nim_nip', $user->nim_nip) }}" required
                                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <div>
                                        <label for="institution" class="block text-sm font-semibold text-slate-700">Instansi
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" id="institution" name="institution"
                                            value="{{ old('institution', $user->institution) }}" required
                                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-semibold text-slate-700">Jenis Customer <span
                                                class="text-red-500">*</span></label>
                                        <div class="mt-2 flex flex-wrap gap-3">
                                            @foreach (\App\Models\ResearchProposal::customerTypes() as $value => $label)
                                                <label
                                                    class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 transition hover:border-emerald-300">
                                                    <input type="radio" name="customer_type"
                                                        value="{{ $value }}"
                                                        {{ old('customer_type') === $value ? 'checked' : '' }}
                                                        class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                    <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('customer_type')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
    
                            {{-- Detail Riset --}}
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-6">
                                <h2 class="text-lg font-bold text-slate-900">Detail Riset &amp; Penelitian</h2>
                                <div class="mt-5 space-y-4">
                                    <div>
                                        <label for="title" class="block text-sm font-semibold text-slate-700">Judul Riset
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" id="title" name="title" value="{{ old('title') }}" required
                                            maxlength="255" placeholder="Contoh: Analisis Kandungan Logam Berat pada Air Sungai"
                                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <div>
                                        <label for="field" class="block text-sm font-semibold text-slate-700">Bidang Penelitian <span class="text-red-500">*</span></label>
                                        <input type="text" id="field" name="field" value="{{ old('field') }}" required
                                            maxlength="100"
                                            placeholder="Contoh: Kimia Analitik, Biologi Molekuler, Fisika Material"
                                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <div>
                                        <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi
                                            Penelitian <span class="text-red-500">*</span></label>
                                        <textarea id="description" name="description" rows="4" required maxlength="5000"
                                            placeholder="Jelaskan latar belakang, metode, dan hal yang ingin diteliti..."
                                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('description') }}</textarea>
                                    </div>
                                    <div>
                                        <label for="objectives" class="block text-sm font-semibold text-slate-700">Tujuan
                                            Penelitian <span
                                                class="text-xs font-normal text-slate-400">(opsional)</span></label>
                                        <textarea id="objectives" name="objectives" rows="3" maxlength="5000"
                                            placeholder="Tuliskan tujuan spesifik dari penelitian ini..."
                                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('objectives') }}</textarea>
                                    </div>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="start_date" class="block text-sm font-semibold text-slate-700">Tanggal
                                                Mulai <span class="text-red-500">*</span></label>
                                            <input type="date" id="start_date" name="start_date"
                                                value="{{ old('start_date') }}" required
                                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                        </div>
                                        <div>
                                            <label for="end_date" class="block text-sm font-semibold text-slate-700">Tanggal
                                                Selesai <span class="text-red-500">*</span></label>
                                            <input type="date" id="end_date" name="end_date"
                                                value="{{ old('end_date') }}" required
                                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700">Membutuhkan Laboran <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                                        <div class="mt-2 flex gap-4">
                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 transition hover:border-emerald-300">
                                                <input type="radio" name="needs_laboran" value="1"
                                                    {{ old('needs_laboran') ? 'checked' : '' }}
                                                    class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-sm font-medium text-slate-700">Ya</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 transition hover:border-emerald-300">
                                                <input type="radio" name="needs_laboran" value="0"
                                                    {{ old('needs_laboran', '0') === '0' ? 'checked' : '' }}
                                                    class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-sm font-medium text-slate-700">Tidak</span>
                                            </label>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-400">Pilih "Ya" jika penelitian Anda membutuhkan bantuan laboran.</p>
                                    </div>
                                    <div>
                                        <label for="document" class="block text-sm font-semibold text-slate-700">Proposal /
                                            Dokumen Pendukung <span
                                                class="text-xs font-normal text-slate-400">(opsional)</span></label>
                                        <input type="file" id="document" name="document"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                            class="mt-1.5 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700 focus:outline-none">
                                        <p class="mt-1 text-xs text-slate-400">PDF, DOC, DOCX, JPG, PNG — maksimal 5 MB.</p>
                                    </div>
                                </div>
                            </div>
    
                            {{-- Bench Fee --}}
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-6">
                                <h2 class="text-lg font-bold text-slate-900">Bench Fee Laboratorium</h2>
                                <p class="mt-1 text-sm text-slate-500">Biaya penggunaan laboratorium selama penelitian,
                                    dihitung otomatis dari jenjang, instansi, kategori, dan durasi (tarif per 3 bulan).</p>
    
                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700">Kategori <span
                                                class="text-red-500">*</span></label>
                                        <div class="mt-2 flex flex-wrap gap-3">
                                            @foreach (\App\Models\ResearchProposal::benchFeeCategories() as $value => $label)
                                                <label
                                                    class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 transition hover:border-emerald-300">
                                                    <input type="radio" name="bench_fee_category"
                                                        value="{{ $value }}"
                                                        {{ old('bench_fee_category') === $value ? 'checked' : '' }}
                                                        class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                    <span
                                                        class="text-sm font-medium text-slate-700">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('bench_fee_category')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700">Jenjang <span
                                                    class="text-red-500">*</span></label>
                                            <div class="mt-2 flex flex-wrap gap-3">
                                                @foreach (['S1' => 'S1', 'S2/S3' => 'S2 / S3'] as $value => $label)
                                                    <label
                                                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 transition hover:border-emerald-300">
                                                        <input type="radio" name="bench_fee_level"
                                                            value="{{ $value }}"
                                                            {{ old('bench_fee_level') === $value ? 'checked' : '' }}
                                                            class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                        <span
                                                            class="text-sm font-medium text-slate-700">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('bench_fee_level')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700">Instansi <span
                                                    class="text-red-500">*</span></label>
                                            <div class="mt-2 flex flex-wrap gap-3">
                                                @foreach (['dalam' => 'Dalam', 'luar' => 'Luar'] as $value => $label)
                                                    <label
                                                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 transition hover:border-emerald-300">
                                                        <input type="radio" name="bench_fee_type"
                                                            value="{{ $value }}"
                                                            {{ old('bench_fee_type') === $value ? 'checked' : '' }}
                                                            class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                        <span
                                                            class="text-sm font-medium text-slate-700">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('bench_fee_type')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
    
                                {{-- Tabel tarif (dinamis dari pengaturan admin) --}}
                                @php $rates = \App\Models\ResearchProposal::benchFeeRates(); @endphp
                                    <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white">
                                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th
                                                        class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                        Jenjang</th>
                                                    <th
                                                        class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                        Instansi</th>
                                                    <th
                                                        class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                        Kategori</th>
                                                    <th
                                                        class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                        Biaya / 3 Bulan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach (['S1' => 'S1', 'S2/S3' => 'S2 / S3'] as $level => $levelLabel)
                                                    @foreach (['dalam' => 'Dalam', 'luar' => 'Luar'] as $type => $typeLabel)
                                                        @foreach (\App\Models\ResearchProposal::benchFeeCategories() as $category => $categoryLabel)
                                                            <tr>
                                                                <td class="px-4 py-2.5 font-medium text-slate-900">
                                                                    {{ $levelLabel }}</td>
                                                                <td class="px-4 py-2.5 text-slate-600">{{ $typeLabel }}
                                                                </td>
                                                                <td class="px-4 py-2.5 text-slate-600">{{ $categoryLabel }}
                                                                </td>
                                                                <td
                                                                    class="px-4 py-2.5 text-right font-semibold text-slate-900">
                                                                    Rp
                                                                    {{ number_format($rates[$level][$type][$category] ?? 0, 0, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
    
                                    {{-- Hasil kalkulasi live --}}
                                    <div id="bench-fee-result"
                                        class="mt-4 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm text-slate-600">
                                        Pilih jenjang, instansi &amp; kategori untuk melihat estimasi bench fee.
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">Estimasi: tarif × jumlah periode 3 bulan (dibulatkan
                                        ke atas) berdasarkan tanggal mulai &amp; selesai.</p>
                                </div>
    
                                {{-- Alat yang Dibutuhkan --}}
                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-6">
                                    <h2 class="text-lg font-bold text-slate-900">Alat yang Dibutuhkan</h2>
                                    <p class="mt-1 text-sm text-slate-500">Cari lalu tambahkan alat laboratorium yang
                                        dibutuhkan untuk penelitian ini (dari katalog kelola alat).</p>
    
                                    {{-- Baris alat dinamis: pilih alat + jumlah --}}
                                    <div id="tools-rows" class="mt-5 space-y-3">                    @php
                        $oldTools = old('tools', []);
                        $oldQtys = old('quantities', []);
                        $oldDays = old('days', []);
                    @endphp
                                        @foreach ($tools as $tool)
                                            @if (in_array($tool->id, $oldTools))
                                                <div
                                                    class="tool-row flex flex-wrap items-center gap-3 rounded-xl border border-emerald-200 bg-white px-4 py-3">
                                                    <select
                                                        class="tool-row-select min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                        <option value="">Pilih alat...</option>
                                                        @foreach ($tools as $t)
                                                            <option value="{{ $t->id }}"
                                                                {{ $t->id === $tool->id ? 'selected' : '' }}>
                                                                {{ $t->name }} ({{ $t->code }}) · Stok:
                                                                {{ $t->available_stock }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="flex items-center gap-2">
                                                        <span class="text-xs font-semibold text-slate-500">Jumlah</span>
                                                        <input type="number"
                                                            class="tool-row-qty w-20 rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                                                            min="1" value="{{ $oldQtys[$tool->id] ?? 1 }}">
                                                    </span>
                                                    <span class="flex items-center gap-2">
                                                        <span class="text-xs font-semibold text-slate-500">Hari</span>
                                                        <input type="number"
                                                            class="tool-row-days w-20 rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                                                            min="1" value="{{ $oldDays[$tool->id] ?? 1 }}">
                                                    </span>
                                                    <button type="button"
                                                        class="remove-tool-row rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>
                                                    <input type="hidden" name="tools[]" value="{{ $tool->id }}">
                                                    <input type="hidden" name="quantities[{{ $tool->id }}]"
                                                        value="{{ $oldQtys[$tool->id] ?? 1 }}">
                                                    <input type="hidden" name="days[{{ $tool->id }}]"
                                                        value="{{ $oldDays[$tool->id] ?? 1 }}">
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
    
                                    <button type="button" id="add-tool-row-btn"
                                        class="mt-4 rounded-lg border border-emerald-600 bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                                        + Tambah Alat
                                    </button>
    
                                    @error('tools')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
    
                                @php
                                    // Data alat untuk pencarian klien (id, nama, kode, kategori, stok, harga/hari).
                                    $toolJson = $tools
                                        ->map(
                                            fn($t) => [
                                                'id' => $t->id,
                                                'name' => $t->name,
                                                'code' => $t->code,
                                                'category' => $t->category_name,
                                                'stock' => $t->available_stock,
                                                'price' => $t->price_per_day,
                                            ],
                                        )
                                        ->values();
                                @endphp
                                <script>
                                    window.__researchTools = @json($toolJson);
                                </script>
                                <script>
                                    window.__benchFeeRates = @json(\App\Models\ResearchProposal::benchFeeRates());
                                </script>
    
                                {{-- Anggota Penelitian --}}
                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-6">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Anggota Penelitian</h2>
                                            <p class="mt-1 text-sm text-slate-500">Tambahkan anggota tim penelitian (termasuk
                                                ketua/pemohon). Bisa manual atau via kode unik.</p>
                                        </div>
                                    </div>

                                    {{-- Tab: Manual / Via Kode Unik --}}
                                    <div class="mt-5 grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1 text-sm font-semibold">
                                        <button type="button" data-member-mode="manual" class="member-mode-btn rounded-lg bg-white px-4 py-2 text-slate-900 shadow-sm">
                                            ✏️ Input Manual
                                        </button>
                                        <button type="button" data-member-mode="code" class="member-mode-btn rounded-lg px-4 py-2 text-slate-500 transition hover:text-slate-700">
                                            🔑 Via Kode Unik
                                        </button>
                                    </div>

                                    {{-- Panel: Via Kode Unik --}}
                                    <div id="member-code-panel" class="hidden mt-4 rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                                        <p class="text-sm font-semibold text-slate-700">Tambahkan via Kode Unik</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Masukkan kode unik partisipan anggota untuk otomatis mengisi data.</p>
                                        <div class="mt-2 flex gap-2">
                                            <input type="text" id="member-code-input" placeholder="ML-XXXXXXXX" autocomplete="off"
                                                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                            <button type="button" id="member-search-btn"
                                                    class="shrink-0 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                                + Tambah
                                            </button>
                                        </div>
                                        <div id="member-error" class="hidden mt-2 rounded-lg border border-red-200 bg-red-50 p-2 text-xs font-semibold text-red-700"></div>
                                    </div>

                                    {{-- Tombol Tambah Manual --}}
                                    <div id="member-manual-btn-wrap" class="mt-4">
                                        <button type="button" id="add-member"
                                            class="rounded-lg border border-emerald-600 bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                                            + Tambah Anggota Manual
                                        </button>
                                        <p class="mt-1 text-xs text-slate-500">Isi nama & peran anggota secara manual.</p>
                                    </div>

                                    <div id="members-list" class="mt-5 space-y-3">
                                        @php
                                            $defaultMembers = old('members', [['name' => $user->name, 'role' => 'Ketua / Pemohon', 'user_id' => $user->id]]);
                                        @endphp
                                        @foreach ($defaultMembers as $index => $member)
                                            <div
                                                class="member-row flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                                                <input type="hidden" name="members[{{ $index }}][user_id]" value="{{ $member['user_id'] ?? '' }}">
                                                <input type="text" name="members[{{ $index }}][name]"
                                                    value="{{ $member['name'] ?? '' }}" placeholder="Nama anggota"
                                                    class="min-w-0 flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                <input type="text" name="members[{{ $index }}][role]"
                                                    value="{{ $member['role'] ?? '' }}" placeholder="Peran (opsional)"
                                                    class="w-44 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                <button type="button"
                                                    class="remove-member rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                                    Hapus
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('members.*.name')
                                        <p class="mt-2 text-xs text-red-600">Nama anggota wajib diisi.</p>
                                    @enderror
                                </div>
    
                                {{-- Surat --}}
                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-6">
                                    <h2 class="text-lg font-bold text-slate-900">Surat Permohonan</h2>
                                    <div class="mt-5 space-y-4">
                                        <div>
                                            <label for="letter" class="block text-sm font-semibold text-slate-700">Surat
                                                Permohonan Penelitian <span class="text-red-500">*</span></label>
                                            <input type="file" id="letter" name="letter"
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required
                                                class="mt-1.5 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700 focus:outline-none">
                                            @error('letter')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                            <p class="mt-1 text-xs text-slate-400">Surat resmi permohonan penelitian dari
                                                institusi/instansi — PDF, DOC, DOCX, JPG, PNG, maksimal 5 MB.</p>
                                        </div>
                                        <div>
                                            <label for="replacement_letter"
                                                class="block text-sm font-semibold text-slate-700">Surat Penggantian Alat <span
                                                    class="text-red-500">*</span></label>
                                            <input type="file" id="replacement_letter" name="replacement_letter"
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required
                                                class="mt-1.5 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700 focus:outline-none">
                                            <p class="mt-1 text-xs text-slate-400">Surat persetujuan penggantian alat bila alat
                                                yang diminta tidak tersedia — wajib diisi.</p>
                                        </div>
                                    </div>
                                </div>
    
                                <div class="flex flex-wrap items-center justify-end gap-3">
                                    <a href="{{ route('research.index') }}"
                                        class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                                        Batal
                                    </a>
                                    <button type="submit"
                                        class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                                        Ajukan Permohonan
                                    </button>
                                </div>
                        </form>
                    </div>
                    {{-- Estimasi Biaya (card kanan) --}}
                    <aside class="md:sticky md:top-24 md:max-h-[calc(100vh-7rem)] md:self-start md:overflow-y-auto">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                                <h2 class="text-lg font-bold text-slate-900">Estimasi Biaya</h2>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">Perkiraan biaya berdasarkan isian Anda, diperbarui otomatis.
                            </p>
    
                            <div class="mt-5 space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-slate-600">Bench Fee Laboratorium</span>
                                    <span id="est-bench-fee" class="text-sm font-bold text-slate-900">-</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-slate-600">Sewa Alat <span id="est-tool-count"
                                            class="text-xs text-slate-400">(0)</span></span>
                                    <span id="est-tools" class="text-sm font-bold text-slate-900">-</span>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-4">
                                    <span class="text-sm font-semibold text-slate-900">Total Estimasi</span>
                                    <span id="est-total" class="text-lg font-extrabold text-emerald-700">-</span>
                                </div>
                            </div>
    
                            <p class="mt-4 rounded-lg bg-slate-50 px-3 py-2.5 text-xs leading-relaxed text-slate-500">
                                Estimasi awal — biaya akhir dikonfirmasi oleh admin saat permohonan disetujui. Sewa alat
                                dihitung dari harga/hari × jumlah × lama penelitian.
                            </p>
                        </div>
                    </aside>
                </div>
                @endauth

                @guest
                <div class="md:col-span-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                        <svg class="mx-auto h-16 w-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <h2 class="mt-4 text-xl font-bold text-slate-900">Formulir Permohonan Riset</h2>
                        <p class="mt-2 text-sm text-slate-500 max-w-lg mx-auto">
                            Untuk mengajukan permohonan riset &amp; penelitian, Anda perlu masuk ke akun Anda terlebih dahulu.
                            Setelah login, Anda dapat mengisi formulir lengkap meliputi informasi pemohon, detail riset,
                            estimasi biaya, alat yang dibutuhkan, anggota penelitian, dan surat permohonan.
                        </p>
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('login') }}" class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                                Masuk untuk Mengajukan
                            </a>
                            <a href="{{ route('register') }}" class="rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                                Belum Punya Akun? Daftar
                            </a>
                        </div>
                    </div>
                </div>
                @endguest
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        // ==== Alat yang Dibutuhkan: baris dinamis (dropdown + jumlah) ====
        (function() {
            const container = document.getElementById('tools-rows');
            const addBtn = document.getElementById('add-tool-row-btn');
            const tools = window.__researchTools || [];

            if (!container || !addBtn) return;

            function byId(id) {
                return tools.find(function(t) {
                    return t.id === id;
                });
            }

            // ID alat yang sedang dipilih di semua baris.
            function selectedIds() {
                const ids = [];
                container.querySelectorAll('.tool-row-select').forEach(function(sel) {
                    const v = sel.value;
                    if (v) ids.push(v);
                });
                return ids;
            }

            // Opsi dropdown: alat yang dipilih baris lain disembunyikan (kecuali milik baris ini).
            function optionsMarkup(keepId) {
                const taken = selectedIds();
                let html = '<option value="">Pilih alat...</option>';

                tools.forEach(function(t) {
                    if (taken.indexOf(t.id) !== -1 && t.id !== keepId) return;
                    html += '<option value="' + t.id + '"' + (t.id === keepId ? ' selected' : '') + '>' +
                        t.name + ' (' + t.code + ') · Stok: ' + t.stock + '</option>';
                });

                return html;
            }

            // Sinkronkan input tersembunyi (tools[], quantities[id], days[id]) dengan isi baris.
            function syncRow(row) {
                row.querySelectorAll('input[type="hidden"]').forEach(function(h) {
                    h.remove();
                });

                const sel = row.querySelector('.tool-row-select');
                const qty = row.querySelector('.tool-row-qty');
                const days = row.querySelector('.tool-row-days');
                const id = sel.value;

                if (id) {
                    row.insertAdjacentHTML('beforeend',
                        '<input type="hidden" name="tools[]" value="' + id + '">' +
                        '<input type="hidden" name="quantities[' + id + ']" value="' + (qty.value || 1) + '">' +
                        '<input type="hidden" name="days[' + id + ']" value="' + (days.value || 1) + '">');
                }
            }

            function refresh() {
                container.querySelectorAll('.tool-row-select').forEach(function(sel) {
                    const cur = sel.value;
                    sel.innerHTML = optionsMarkup(cur || null);
                });
                container.querySelectorAll('.tool-row').forEach(syncRow);
                if (window.__updateCostSummary) window.__updateCostSummary();
            }

            function rowMarkup() {
                return '<div class="tool-row flex flex-wrap items-center gap-3 rounded-xl border border-emerald-200 bg-white px-4 py-3">' +
                    '<select class="tool-row-select min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">' +
                    optionsMarkup(null) + '</select>' +
                    '<span class="flex items-center gap-2">' +
                    '<span class="text-xs font-semibold text-slate-500">Jumlah</span>' +
                    '<input type="number" class="tool-row-qty w-20 rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" min="1" value="1">' +
                    '</span>' +
                    '<span class="flex items-center gap-2">' +
                    '<span class="text-xs font-semibold text-slate-500">Hari</span>' +
                    '<input type="number" class="tool-row-days w-20 rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" min="1" value="1">' +
                    '</span>' +
                    '<button type="button" class="remove-tool-row rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>' +
                    '</div>';
            }

            container.addEventListener('change', function(e) {
                if (e.target.classList.contains('tool-row-select')) refresh();
            });

            container.addEventListener('input', function(e) {
                if (e.target.classList.contains('tool-row-qty') || e.target.classList.contains('tool-row-days')) {
                    syncRow(e.target.closest('.tool-row'));
                    if (window.__updateCostSummary) window.__updateCostSummary();
                }
            });

            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-tool-row')) {
                    e.target.closest('.tool-row').remove();
                    refresh();
                }
            });

            addBtn.addEventListener('click', function() {
                container.insertAdjacentHTML('beforeend', rowMarkup());
            });

            refresh();
        })();

        // ==== Bench Fee & Estimasi Biaya: kalkulasi live ====
        (function() {
            const start = document.getElementById('start_date');
            const end = document.getElementById('end_date');
            const result = document.getElementById('bench-fee-result');
            const rates = window.__benchFeeRates || {};

            if (!start || !end || !result) return;

            function fmt(n) {
                return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            // Total bench fee (null bila belum bisa dihitung).
            function benchFeeTotal() {
                const level = document.querySelector('input[name="bench_fee_level"]:checked');
                const type = document.querySelector('input[name="bench_fee_type"]:checked');
                const category = document.querySelector('input[name="bench_fee_category"]:checked');

                if (!level || !type || !category) return null;

                const rate = ((rates[level.value] || {})[type.value] || {})[category.value];
                if (typeof rate !== 'number') return null;

                if (!start.value || !end.value) return null;

                const s = new Date(start.value);
                const e = new Date(end.value);
                if (e < s) return null;

                const months = Math.max(1, (e.getFullYear() - s.getFullYear()) * 12 + (e.getMonth() - s.getMonth()) +
                1);
                const periods = Math.ceil(months / 3);

                return rate * periods;
            }

            // Jumlah hari penelitian (selisih tanggal), minimal 1.
            function researchDays() {
                if (!start.value || !end.value) return 0;
                const s = new Date(start.value);
                const e = new Date(end.value);
                if (e < s) return 0;
                return Math.max(1, Math.round((e - s) / 86400000));
            }

            function renderBenchFee() {
                const level = document.querySelector('input[name="bench_fee_level"]:checked');
                const type = document.querySelector('input[name="bench_fee_type"]:checked');
                const category = document.querySelector('input[name="bench_fee_category"]:checked');
                const total = benchFeeTotal();

                if (!level || !type || !category) {
                    result.className =
                        'mt-4 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm text-slate-600';
                    result.textContent = 'Pilih jenjang, instansi & kategori untuk melihat estimasi bench fee.';
                    return;
                }

                if (total === null) {
                    if (!start.value || !end.value) {
                        result.className =
                            'mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700';
                        result.textContent = 'Lengkapi tanggal mulai & selesai untuk menghitung bench fee.';
                    } else {
                        result.className =
                            'mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600';
                        result.textContent = 'Tanggal selesai harus setelah tanggal mulai.';
                    }
                    return;
                }

                const rate = ((rates[level.value] || {})[type.value] || {})[category.value];
                const s = new Date(start.value);
                const e = new Date(end.value);
                const months = Math.max(1, (e.getFullYear() - s.getFullYear()) * 12 + (e.getMonth() - s.getMonth()) +
                1);
                const periods = Math.ceil(months / 3);

                result.className =
                    'mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800';
                result.innerHTML = '<span class="font-bold">Bench Fee: ' + fmt(total) + '</span>' +
                    '<span class="ml-2 text-xs text-emerald-700">(' + level.value + ' · ' +
                    (type.value === 'dalam' ? 'Dalam' : 'Luar') + ' · ' +
                    (category.value === 'biomedis' ? 'Biomedis' : 'Non-Biomedis') + ' · ' + fmt(rate) + ' × ' +
                    periods + ' periode 3 bulan, ' + months + ' bulan)</span>';
            }

            // Perbarui card estimasi biaya di kanan.
            function updateSummary() {
                const bench = window.__benchFeeTotal ? window.__benchFeeTotal() : null;
                const days = window.__researchDays ? window.__researchDays() : 0;
                const tools = window.__researchTools || [];

                let toolCost = 0;
                let toolCount = 0;

                document.querySelectorAll('#tools-rows .tool-row').forEach(function(row) {
                    const sel = row.querySelector('.tool-row-select');
                    const qty = row.querySelector('.tool-row-qty');
                    const daysInput = row.querySelector('.tool-row-days');
                    const id = sel ? sel.value : '';
                    if (!id) return;

                    const t = tools.find(function(x) {
                        return x.id === id;
                    });
                    if (!t) return;

                    const q = Math.max(0, parseInt(qty ? qty.value : '0', 10) || 0);
                    const d = Math.max(0, parseInt(daysInput ? daysInput.value : '0', 10) || 0);
                    toolCost += (t.price || 0) * q * d;
                    toolCount += q;
                });

                const estBench = document.getElementById('est-bench-fee');
                const estTools = document.getElementById('est-tools');
                const estTotal = document.getElementById('est-total');
                const estCount = document.getElementById('est-tool-count');

                if (estBench) estBench.textContent = bench !== null ? fmt(bench) : '-';
                if (estTools) estTools.textContent = (days > 0 && toolCost > 0) ? fmt(toolCost) : '-';
                if (estCount) estCount.textContent = '(' + toolCount + ')';
                if (estTotal) estTotal.textContent = (bench !== null || toolCost > 0) ? fmt((bench || 0) + toolCost) :
                    '-';
            }

            window.__benchFeeTotal = benchFeeTotal;
            window.__researchDays = researchDays;
            window.__updateCostSummary = updateSummary;

            ['start_date', 'end_date'].forEach(function(id) {
                document.getElementById(id).addEventListener('change', function() {
                    renderBenchFee();
                    updateSummary();
                });
            });

            document.querySelectorAll(
                    'input[name="bench_fee_level"], input[name="bench_fee_type"], input[name="bench_fee_category"]')
                .forEach(function(r) {
                    r.addEventListener('change', function() {
                        renderBenchFee();
                        updateSummary();
                    });
                });

            renderBenchFee();
            updateSummary();
        })();

        // ==== Anggota Penelitian ====
        (function() {
            const list = document.getElementById('members-list');
            const addBtn = document.getElementById('add-member');
            const searchUrl = @json(route('research.search-member'));

            // Tab mode: Manual / Kode Unik
            var codePanel = document.getElementById('member-code-panel');
            var manualBtnWrap = document.getElementById('member-manual-btn-wrap');
            document.querySelectorAll('.member-mode-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var mode = btn.dataset.memberMode;
                    document.querySelectorAll('.member-mode-btn').forEach(function(b) {
                        var active = b.dataset.memberMode === mode;
                        b.classList.toggle('bg-white', active);
                        b.classList.toggle('text-slate-900', active);
                        b.classList.toggle('shadow-sm', active);
                        b.classList.toggle('text-slate-500', !active);
                    });
                    if (mode === 'code') {
                        codePanel.classList.remove('hidden');
                        manualBtnWrap.classList.add('hidden');
                    } else {
                        codePanel.classList.add('hidden');
                        manualBtnWrap.classList.remove('hidden');
                    }
                });
            });

            function rowMarkup(index, data) {
                data = data || {};
                var userId = data.user_id || '';
                var name = data.name || '';
                var role = data.role || '';
                return '<div class="member-row flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">' +
                    '<input type="hidden" name="members[' + index + '][user_id]" value="' + userId + '">' +
                    '<input type="text" name="members[' + index + '][name]" placeholder="Nama anggota" value="' + name + '"' +
                    ' class="min-w-0 flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">' +
                    '<input type="text" name="members[' + index + '][role]" placeholder="Peran (opsional)" value="' + role + '"' +
                    ' class="w-44 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">' +
                    '<button type="button" class="remove-member rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>' +
                    '</div>';
            }

            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    const rows = list.querySelectorAll('.member-row');
                    list.insertAdjacentHTML('beforeend', rowMarkup(rows.length));
                });
            }

            list.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-member')) {
                    e.target.closest('.member-row').remove();
                    // Renomor ulang indeks agar validasi & penyimpanan konsisten.
                    list.querySelectorAll('.member-row').forEach(function(row, i) {
                        row.querySelectorAll('input').forEach(function(input) {
                            input.name = input.name.replace(/\[\d+\]/, '[' + i + ']');
                        });
                    });
                }
            });

            // Tambah anggota via kode unik
            var codeInput = document.getElementById('member-code-input');
            var searchBtn = document.getElementById('member-search-btn');
            var errorEl = document.getElementById('member-error');

            if (searchBtn && codeInput) {
                searchBtn.addEventListener('click', function() {
                    var code = codeInput.value.trim();
                    errorEl.classList.add('hidden');

                    if (!code) {
                        errorEl.textContent = 'Masukkan kode unik anggota terlebih dahulu.';
                        errorEl.classList.remove('hidden');
                        return;
                    }

                    // Cek apakah kode sudah ditambahkan
                    var existingCodes = [];
                    list.querySelectorAll('input[name*="[user_id]"]').forEach(function(h) {
                        if (h.value) existingCodes.push(h.value);
                    });

                    searchBtn.disabled = true;
                    searchBtn.textContent = 'Mencari...';

                    fetch(searchUrl + '?kode=' + encodeURIComponent(code), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (!data.found) {
                            errorEl.textContent = 'Kode tidak ditemukan atau bukan akun peserta.';
                            errorEl.classList.remove('hidden');
                            return;
                        }

                        // Cek duplikat
                        if (existingCodes.indexOf(String(data.user_id)) !== -1) {
                            errorEl.textContent = 'Anggota ini sudah ditambahkan.';
                            errorEl.classList.remove('hidden');
                            return;
                        }

                        var rows = list.querySelectorAll('.member-row');
                        var idx = rows.length;
                        list.insertAdjacentHTML('beforeend', rowMarkup(idx, {
                            user_id: data.user_id,
                            name: data.name
                        }));

                        codeInput.value = '';
                    })
                    .catch(function() {
                        errorEl.textContent = 'Terjadi kesalahan. Coba lagi.';
                        errorEl.classList.remove('hidden');
                    })
                    .finally(function() {
                        searchBtn.disabled = false;
                        searchBtn.textContent = '+ Tambah';
                    });
                });

                codeInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchBtn.click();
                    }
                });
            }
        })();
    </script>
@endpush
