@extends('layouts.admin')

@section('title', 'Detail Booking ' . $checkup->code . ' - MarketLabs')

@section('page', 'Pemeriksaan Kesehatan')

@section('content')

<a href="{{ route('admin.health-checkups.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
    ← Kembali ke Daftar Booking
</a>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

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

<div class="mt-6 flex flex-wrap items-center gap-3">
    <span class="text-sm font-bold text-slate-500">{{ $checkup->code }}</span>
    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ $checkup->queue_label }}</span>
    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($checkup->status) {
        'pending' => 'bg-amber-50 text-amber-700',
        'approved' => 'bg-sky-50 text-sky-700',
        'done' => 'bg-emerald-50 text-emerald-700',
        'rejected' => 'bg-red-50 text-red-600',
        'cancelled' => 'bg-slate-100 text-slate-500',
        default => 'bg-slate-100 text-slate-500',
    } }}">
        {{ $checkup->status_label }}
    </span>
    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $checkup->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
        {{ $checkup->payment_status_label }}
    </span>
</div>

<h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">{{ $checkup->type?->name ?? 'Pemeriksaan' }}</h1>
<p class="mt-1 text-sm text-slate-600">{{ $checkup->booking_date->translatedFormat('l, d M Y') }} · {{ $checkup->formatted_price }}</p>

<div class="mt-8 grid gap-6 lg:grid-cols-3">
    {{-- Kiri: informasi & hasil --}}
    <div class="space-y-6 lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Informasi Booking</h2>
            </div>
            <div class="grid gap-6 px-6 py-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pasien</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $checkup->user->name }}</p>
                    <p class="text-xs text-slate-500">{{ $checkup->user->email }}</p>
                    <p class="text-xs text-slate-500">{{ $checkup->user->institution ?? '-' }} · {{ $checkup->user->nim_nip ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Pemeriksaan</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $checkup->type?->name ?? '-' }}</p>
                    <p class="text-xs text-slate-500">{{ $checkup->formatted_price }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pemeriksa</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $checkup->examiner?->name ?? 'Belum ditugaskan' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal &amp; Antrian</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $checkup->booking_date->translatedFormat('d M Y') }}</p>
                    <p class="text-xs text-slate-500">Nomor antrian {{ $checkup->queue_label }}</p>
                </div>
                @if ($checkup->purpose)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tujuan</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $checkup->purpose }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Hasil Pemeriksaan</h2>
            </div>
            <form action="{{ route('admin.health-checkups.result', $checkup) }}" method="POST" enctype="multipart/form-data" class="space-y-4 px-6 py-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="result" class="block text-sm font-semibold text-slate-700">Hasil <span class="text-red-500">*</span></label>
                    <input type="text" id="result" name="result" list="result-suggestions"
                           value="{{ old('result', $checkup->result) }}" maxlength="255" required
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <datalist id="result-suggestions">
                        @if ($checkup->type?->key === 'hbsag')
                            <option value="Negatif"></option>
                            <option value="Reaktif"></option>
                        @else
                            <option value="Negatif"></option>
                            <option value="Positif"></option>
                        @endif
                    </datalist>
                    <p class="mt-1.5 text-xs text-slate-500">
                        Saran: {{ $checkup->type?->key === 'hbsag' ? 'Negatif / Reaktif' : 'Negatif / Positif' }} (atau isi nilai lain sesuai hasil).
                    </p>
                </div>

                <div>
                    <label for="result_notes" class="block text-sm font-semibold text-slate-700">Keterangan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="result_notes" name="result_notes" rows="3" maxlength="2000"
                              class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('result_notes', $checkup->result_notes) }}</textarea>
                </div>

                <div>
                    <label for="result_file" class="block text-sm font-semibold text-slate-700">File Hasil Pemeriksaan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="file" id="result_file" name="result_file"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                           class="mt-1.5 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700 focus:outline-none">
                    <p class="mt-1 text-xs text-slate-400">PDF, DOC, DOCX, JPG, PNG — maksimal 5 MB.</p>
                    @if ($checkup->result_file)
                        <a href="{{ route('health-checkups.result-download', $checkup) }}" target="_blank"
                           class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 transition hover:text-emerald-800">
                            📄 Lihat file hasil saat ini
                        </a>
                    @endif
                </div>

                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Simpan Hasil
                </button>
            </form>
        </div>
    </div>

    {{-- Kanan: status & pembayaran --}}
    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Ubah Status</h2>
            </div>
            <form action="{{ route('admin.health-checkups.status', $checkup) }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PATCH')

                <div class="space-y-2">
                    @foreach ([
                        'approved' => 'Konfirmasi Booking',
                        'done' => 'Tandai Selesai',
                        'rejected' => 'Tolak',
                        'cancelled' => 'Batalkan',
                    ] as $value => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 transition hover:border-emerald-300">
                            <input type="radio" name="status" value="{{ $value }}"
                                   {{ $checkup->status === $value ? 'checked' : '' }}
                                   class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <div>
                    <label for="admin_notes" class="block text-sm font-semibold text-slate-700">Catatan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="admin_notes" name="admin_notes" rows="3" maxlength="2000"
                              placeholder="Alasan penolakan, keterangan, dll..."
                              class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('admin_notes') }}</textarea>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Simpan Status
                </button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Status Pembayaran</h2>
            </div>
            <div class="space-y-3 px-6 py-6 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Total Tagihan</span>
                    <span class="font-bold text-slate-900">{{ $checkup->formatted_price }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Status</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $checkup->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $checkup->payment_status_label }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">No. Invoice</span>
                    <span class="font-semibold text-slate-900">{{ $checkup->invoice_number ?? '-' }}</span>
                </div>
                @if ($checkup->invoice_number)
                    <a href="{{ route('admin.health-checkups.invoice', $checkup) }}" target="_blank"
                       class="inline-block rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                        Lihat Invoice
                    </a>
                @endif
            </div>
            <form action="{{ route('admin.health-checkups.payment', $checkup) }}" method="POST" class="flex gap-2 border-t border-slate-100 px-6 py-4">
                @csrf
                @method('PATCH')
                @if (! $checkup->is_paid)
                    <button type="submit" name="payment_status" value="paid"
                            class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                        Tandai Lunas
                    </button>
                @endif
                @if ($checkup->is_paid)
                    <button type="submit" name="payment_status" value="unpaid"
                            class="flex-1 rounded-lg bg-amber-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-600">
                        Tandai Belum Bayar
                    </button>
                @endif
            </form>
        </div>
    </div>
</div>

@endsection
