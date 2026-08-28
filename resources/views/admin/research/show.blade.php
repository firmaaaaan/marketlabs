@extends('layouts.admin')

@section('title', 'Detail Permohonan Riset - MarketLabs')

@section('page', 'Permohonan Riset')

@section('content')

<a href="{{ route('admin.research.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
    ← Kembali ke Daftar Permohonan
</a>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

<div class="mt-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
        <span class="text-sm font-bold text-slate-500">{{ $proposal->code }}</span>
        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($proposal->status) {
            'pending' => 'bg-amber-50 text-amber-700',
            'approved' => 'bg-sky-50 text-sky-700',
            'ongoing' => 'bg-indigo-50 text-indigo-700',
            'rejected' => 'bg-red-50 text-red-600',
            'done' => 'bg-emerald-50 text-emerald-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
            default => 'bg-slate-100 text-slate-500',
        } }}">
            {{ $proposal->status_label }}
        </span>
        <span class="text-xs text-slate-400">Diajukan {{ $proposal->created_at->diffForHumans() }}</span>
    </div>
    <a href="{{ route('admin.research.logbook', $proposal) }}"
       class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700">
        Logbook Penelitian
    </a>
</div>

<h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">{{ $proposal->title }}</h1>

<div class="mt-8 grid gap-6 lg:grid-cols-3">

    {{-- Informasi Pemohon + Detail Permohonan (combined) --}}
    <div class="space-y-6 lg:col-span-2">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold text-slate-900">Informasi Pemohon</h2>
            </div>
            <div class="grid gap-5 px-6 py-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->user->email }}</p>
                </div>
                @if ($proposal->nim_nip)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">NIM / NIP / NIDN / NIK</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->nim_nip }}</p>
                    </div>
                @endif
                @if ($proposal->institution)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Instansi</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->institution }}</p>
                    </div>
                @endif
                @if ($proposal->customer_type)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Customer</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->customer_type_label }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Detail Permohonan --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold text-slate-900">Detail Permohonan</h2>
            </div>
            <div class="grid gap-5 px-6 py-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bidang Penelitian</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->field ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Periode</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">
                        @if ($proposal->start_date && $proposal->end_date)
                            {{ $proposal->start_date->translatedFormat('d M Y') }} — {{ $proposal->end_date->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                @if ($proposal->bench_fee !== null)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bench Fee Laboratorium</p>
                        <p class="mt-1 text-sm font-bold text-emerald-700">{{ $proposal->formatted_bench_fee }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $proposal->bench_fee_level }} · Instansi {{ $proposal->bench_fee_type_label }} · Kategori {{ $proposal->bench_fee_category_label }} · tarif per 3 bulan
                        </p>
                    </div>
                @endif
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Membutuhkan Laboran</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->needs_laboran ? 'Ya' : 'Tidak' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Deskripsi Penelitian</p>
                    <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $proposal->description ?? '-' }}</p>
                </div>
                @if ($proposal->objectives)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tujuan Penelitian</p>
                        <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $proposal->objectives }}</p>
                    </div>
                @endif
                @if ($proposal->document_path)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dokumen Pendukung</p>
                        <a href="{{ route('research.document', [$proposal, 'document']) }}" target="_blank"
                           class="mt-1 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m3 3V7a2 2 0 00-2-2h-6a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2z" />
                            </svg>
                            {{ $proposal->document_name }}
                        </a>
                    </div>
                @endif
                @if ($proposal->letter_path)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Surat Permohonan</p>
                        <a href="{{ route('research.document', [$proposal, 'letter']) }}" target="_blank"
                           class="mt-1 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m3 3V7a2 2 0 00-2-2h-6a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2z" />
                            </svg>
                            {{ $proposal->letter_name }}
                        </a>
                    </div>
                @endif
                @if ($proposal->replacement_letter_path)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Surat Penggantian Alat</p>
                        <a href="{{ route('research.document', [$proposal, 'replacement']) }}" target="_blank"
                           class="mt-1 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m3 3V7a2 2 0 00-2-2h-6a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2z" />
                            </svg>
                            {{ $proposal->replacement_letter_name }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Anggota Penelitian --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold text-slate-900">Anggota Penelitian</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ $proposal->members->count() }} anggota tim penelitian.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="w-14 px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">No</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Peran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($proposal->members as $index => $member)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $member->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $member->role ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-500">Tidak ada anggota tercantum.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Alat yang Dibutuhkan --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold text-slate-900">Alat yang Dibutuhkan</h2>
                <p class="mt-0.5 text-xs text-slate-500">Subtotal dihitung dari harga/hari × jumlah × hari penggunaan per alat.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Alat</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kode / Kategori</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Jumlah</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Hari</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Harga / Hari</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Subtotal Sewa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($proposal->tools as $tool)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $tool->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $tool->code }} · {{ $tool->category_name }}</td>
                                <td class="px-6 py-4 text-right text-slate-600">{{ $tool->pivot->quantity }} unit</td>
                                <td class="px-6 py-4 text-right text-slate-600">{{ $tool->pivot->days }}</td>
                                <td class="px-6 py-4 text-right text-slate-600">Rp {{ number_format($tool->price_per_day, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-slate-900">Rp {{ number_format($tool->price_per_day * $tool->pivot->quantity * $tool->pivot->days, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">Tidak ada alat yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan biaya --}}
            <div class="space-y-2 border-t border-slate-100 px-6 py-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Total Sewa Alat</span>
                    <span class="font-semibold text-slate-900">{{ $proposal->formatted_tools_subtotal }}</span>
                </div>
                @if ($proposal->bench_fee !== null)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Bench Fee Laboratorium</span>
                        <span class="font-semibold text-slate-900">{{ $proposal->formatted_bench_fee }}</span>
                    </div>
                @endif
                @if ($proposal->laboran_fee !== null)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Biaya Laboran</span>
                        <span class="font-semibold text-slate-900">{{ $proposal->formatted_laboran_fee }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-base font-bold text-emerald-700">
                    <span>Total Keseluruhan</span>
                    <span>{{ $proposal->formatted_grand_total }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Penugasan laboran & laboratorium --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold text-slate-900">Penugasan Laboran &amp; Laboratorium</h2>
            </div>
            <form action="{{ route('admin.research.assignment', $proposal) }}" method="POST" class="space-y-4 px-6 py-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="laboran_id" class="block text-sm font-semibold text-slate-700">Laboran <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <select id="laboran_id" name="laboran_id"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <option value="">— Pilih Laboran —</option>
                        @foreach ($laborans as $laboran)
                            <option value="{{ $laboran->id }}" {{ $proposal->laboran_id === $laboran->id ? 'selected' : '' }}>{{ $laboran->name }} ({{ $laboran->email }})</option>
                        @endforeach
                    </select>
                    @if ($laborans->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">Belum ada user berperan laboran. Tambahkan lewat <a href="{{ route('admin.users.index') }}" class="font-semibold underline">Kelola User</a>.</p>
                    @endif
                </div>

                <div>
                    <label for="laboran_fee" class="block text-sm font-semibold text-slate-700">Biaya Laboran (Rp) <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="number" id="laboran_fee" name="laboran_fee" value="{{ old('laboran_fee', $proposal->laboran_fee) }}" min="0" step="1"
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label for="laboratorium_id" class="block text-sm font-semibold text-slate-700">Laboratorium <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <select id="laboratorium_id" name="laboratorium_id"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <option value="">— Pilih Laboratorium —</option>
                        @foreach ($laboratoriums as $lab)
                            <option value="{{ $lab->id }}" {{ $proposal->laboratorium_id === $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                        @endforeach
                    </select>
                    @if ($laboratoriums->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">Belum ada laboratorium. Tambahkan lewat <a href="{{ route('admin.laboratoriums.index') }}" class="font-semibold underline">Kelola Laboratorium</a>.</p>
                    @endif
                </div>

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <ul class="list-inside list-disc space-y-1 text-xs text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button type="submit"
                        class="w-full rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                    Simpan Penugasan
                </button>
            </form>
        </div>

        {{-- Denda --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold text-slate-900">Denda &amp; Biaya Tambahan</h2>
            </div>
            <form action="{{ route('admin.research.penalty', $proposal) }}" method="POST" class="space-y-4 px-6 py-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="penalty" class="block text-sm font-semibold text-slate-700">Denda (Rp) <span class="text-xs font-normal text-slate-400">(kerusakan alat, keterlambatan, dll.)</span></label>
                    <input type="number" id="penalty" name="penalty" value="{{ old('penalty', $proposal->penalty) }}" min="0" step="1000"
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label for="penalty_note" class="block text-sm font-semibold text-slate-700">Keterangan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="penalty_note" name="penalty_note" rows="2"
                              placeholder="Contoh: 1 unit mikroskop rusak saat penelitian"
                              class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('penalty_note', $proposal->penalty_note) }}</textarea>
                </div>

                @error('penalty')
                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="w-full rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
                    Simpan Denda
                </button>
            </form>
        </div>

        {{-- Status Pembayaran --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold text-slate-900">Status Pembayaran</h2>
            </div>
            <div class="space-y-3 px-6 py-5 text-sm">
                @if ($proposal->penalty > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Denda / Biaya Tambahan</span>
                        <span class="font-semibold text-amber-600">{{ $proposal->formatted_penalty }}</span>
                    </div>
                    @if ($proposal->penalty_note)
                        <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700">{{ $proposal->penalty_note }}</p>
                    @endif
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Total Tagihan</span>
                    <span class="font-bold text-slate-900">{{ $proposal->formatted_grand_total }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Status</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $proposal->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $proposal->payment_status_label }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">No. Invoice</span>
                    <span class="font-semibold text-slate-900">{{ $proposal->invoice_number ?? '-' }}</span>
                </div>
                @if ($proposal->invoice_number)
                    <a href="{{ route('admin.research.invoice', $proposal) }}" target="_blank"
                       class="inline-block rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                        Lihat Invoice
                    </a>
                @endif
            </div>
            <form action="{{ route('admin.research.payment', $proposal) }}" method="POST" class="flex gap-2 border-t border-slate-100 px-6 py-4">
                @csrf
                @method('PATCH')
                @if (! $proposal->is_paid)
                    <button type="submit" name="payment_status" value="paid"
                            class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                        Tandai Lunas
                    </button>
                @endif
                @if ($proposal->is_paid)
                    <button type="submit" name="payment_status" value="unpaid"
                            class="flex-1 rounded-lg bg-amber-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-600">
                        Tandai Belum Bayar
                    </button>
                @endif
            </form>
        </div>

        {{-- Ubah Status --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold text-slate-900">Ubah Status</h2>
            </div>
            <form action="{{ route('admin.research.status', $proposal) }}" method="POST" class="space-y-4 px-6 py-5">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Status <span class="text-red-500">*</span></label>
                    <div class="mt-2 space-y-2">
                        @foreach ([
                            'approved' => 'Setujui',
                            'ongoing' => 'Tandai Sedang Berlangsung',
                            'rejected' => 'Tolak',
                            'done' => 'Tandai Selesai',
                        ] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 transition hover:border-emerald-300">
                                <input type="radio" name="status" value="{{ $value }}"
                                       {{ $proposal->status === $value ? 'checked' : '' }}
                                       class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admin_notes" class="block text-sm font-semibold text-slate-700">Catatan Admin <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="admin_notes" name="admin_notes" rows="4" maxlength="2000"
                              placeholder="Catatan untuk pemohon (mis. alasan penolakan)..."
                              class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('admin_notes', $proposal->admin_notes) }}</textarea>
                    @error('admin_notes')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        @if ($proposal->admin_notes)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                <h2 class="text-sm font-bold text-amber-800">Catatan Admin Saat Ini</h2>
                <p class="mt-2 text-sm whitespace-pre-line text-amber-900">{{ $proposal->admin_notes }}</p>
            </div>
        @endif
    </div>
</div>

@endsection
