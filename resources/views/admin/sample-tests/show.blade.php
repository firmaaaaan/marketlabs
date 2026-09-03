@extends('layouts.admin')

@section('title', 'Detail Pengujian Sampel - MarketLabs')

@section('page', 'Kelola Pengujian')

@section('content')

<a href="{{ route('admin.sample-tests.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
    ← Kembali ke Daftar Pengujian
</a>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

<div class="mt-6 flex flex-wrap items-center gap-3">
    <span class="text-sm font-bold text-slate-500">{{ $test->code }}</span>
    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($test->status) {
        'pending' => 'bg-amber-50 text-amber-700',
        'approved' => 'bg-sky-50 text-sky-700',
        'received' => 'bg-teal-50 text-teal-700',
        'testing' => 'bg-indigo-50 text-indigo-700',
        'done' => 'bg-emerald-50 text-emerald-700',
        'rejected' => 'bg-red-50 text-red-600',
        'cancelled' => 'bg-slate-100 text-slate-500',
        default => 'bg-slate-100 text-slate-500',
    } }}">
        {{ $test->status_label }}
    </span>
    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $test->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
        {{ $test->payment_status_label }}
    </span>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.sample-tests.print', $test) }}" target="_blank"
           class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
            🖨️ Cetak Sampel
        </a>
        <a href="{{ route('admin.sample-tests.edit', $test) }}"
           class="rounded-lg bg-slate-100 p-2 text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700"
           title="Edit">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
        </a>
        <form action="{{ route('admin.sample-tests.destroy', $test) }}" method="POST"
              data-confirm="Hapus pengujian {{ $test->code }}?" data-confirm-accept="Ya, Hapus">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="rounded-lg bg-red-50 p-2 text-red-600 transition hover:bg-red-100"
                    title="Hapus">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </button>
        </form>
    </div>
</div>

<h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">Detail Pengujian Sampel</h1>
<p class="mt-1 text-sm text-slate-600">{{ $test->items->count() }} sampel · {{ $test->services_count }} layanan · {{ $test->units_label }}</p>

<div class="mt-8 grid gap-6 lg:grid-cols-3">
    {{-- Kiri: informasi & parameter --}}
    <div class="space-y-6 lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Informasi Pengujian</h2>
            </div>
            <div class="grid gap-6 px-6 py-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pemohon</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $test->user->name }}</p>
                    <p class="text-xs text-slate-500">{{ $test->user->email }}</p>
                    <p class="text-xs text-slate-500">{{ $test->user->institution ?? '-' }} · {{ $test->user->nim_nip ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Daftar Sampel</p>
                    <ul class="mt-1 space-y-1 text-sm">
                        @forelse ($test->items as $index => $item)
                            <li class="font-medium text-slate-900">
                                <span class="text-xs font-semibold text-emerald-700">{{ $test->code }}-{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                • {{ $item->sample_name }} <span class="text-xs font-normal text-slate-500">({{ $item->quantity }} unit)</span>
                            </li>
                        @empty
                            <li class="text-slate-500">Tidak ada sampel.</li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pengiriman Sampel</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $test->delivery_method_label }}</p>
                </div>
                @if ($test->notes)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan Pemohon</p>
                        <p class="mt-1 whitespace-pre-line text-sm font-medium text-slate-900">{{ $test->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            @php $sampleCounter = 0; @endphp
            @forelse ($test->items->groupBy('parameter_id') as $parameterId => $items)
                @php
                    $parameter = $items->first()->parameter;
                    $serviceSubtotal = $items->sum(fn ($i) => $i->subtotal);
                @endphp
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-emerald-50/60 px-6 py-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $parameter?->name ?? 'Layanan' }}</h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                @if ($parameter?->method)
                                    Metode: {{ $parameter->method }} ·
                                @endif
                                Satuan: {{ $parameter?->unit?->name ?? '-' }}
                            </p>
                        </div>
                        <span class="text-sm font-extrabold text-emerald-700">{{ $parameter?->formatted_rate ?? '' }}</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($items as $index => $item)
                            <div class="flex items-center justify-between gap-4 px-6 py-3.5">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900">
                                        @php $sampleCounter++; @endphp
                                        <span class="text-xs font-semibold text-emerald-700">{{ $test->code }}-{{ str_pad($sampleCounter, 2, '0', STR_PAD_LEFT) }}</span>
                                        · {{ $item->sample_name }}
                                        <span class="text-xs font-normal text-slate-400">({{ $item->quantity }} unit)</span>
                                    </p>
                                    @if ($item->form_label || $item->type_label)
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            @if ($item->form_label)
                                                Bentuk: {{ $item->form_label }}
                                            @endif
                                            @if ($item->type_label)
                                                {{ $item->form_label ? '·' : '' }} Jenis: {{ $item->type_label }}
                                            @endif
                                        </p>
                                    @endif
                                    @if ($item->sample_description)
                                        <p class="mt-0.5 whitespace-pre-line text-xs text-slate-500">{{ $item->sample_description }}</p>
                                    @endif
                                </div>
                                <span class="flex-none text-sm font-semibold text-slate-700">{{ $item->formatted_subtotal }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-3">
                        <span class="text-xs font-bold text-slate-500">Subtotal {{ $parameter?->name ?? 'Layanan' }}</span>
                        <span class="text-sm font-extrabold text-slate-900">Rp {{ number_format($serviceSubtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
                    <p class="text-sm text-slate-500">Tidak ada sampel pada pengujian ini.</p>
                </div>
            @endforelse

            <div class="flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4">
                <span class="text-sm font-bold text-slate-900">Total Keseluruhan</span>
                <span class="text-xl font-extrabold text-emerald-700">{{ $test->formatted_total_cost }}</span>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Hasil Pengujian</h2>
            </div>
            <form action="{{ route('admin.sample-tests.result', $test) }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="result" class="block text-sm font-semibold text-slate-700">Hasil <span class="text-xs font-normal text-slate-400">(contoh: Kadar air 8,2%)</span></label>
                    <input type="text" id="result" name="result" value="{{ old('result', $test->result) }}" maxlength="255"
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    @error('result')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="result_notes" class="block text-sm font-semibold text-slate-700">Keterangan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="result_notes" name="result_notes" rows="3"
                              class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('result_notes', $test->result_notes) }}</textarea>
                    @error('result_notes')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Simpan Hasil
                </button>
            </form>

            <div class="border-t border-slate-100 px-6 py-5">
                <p class="text-sm font-semibold text-slate-700">Upload Dokumen Hasil <span class="text-xs font-normal text-slate-400">(PDF / DOC / gambar, maks 10 MB)</span></p>

                @if ($test->result_file)
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-emerald-50 px-4 py-3">
                        <a href="{{ route('sample-tests.result-download', $test) }}" target="_blank"
                           class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 transition hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Lihat Dokumen Hasil
                        </a>
                        <span class="text-xs text-slate-500">Diunggah, upload baru untuk mengganti.</span>
                    </div>
                @endif

                <form action="{{ route('admin.sample-tests.result-file', $test) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <div class="flex flex-wrap items-center gap-3">
                        <input type="file" name="result_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required
                               class="block w-full max-w-xs text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                        <button type="submit"
                                class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            Upload Hasil
                        </button>
                    </div>
                    @error('result_file')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>
    </div>

    {{-- Kanan: status, penugasan & pembayaran --}}
    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Penugasan Laboran</h2>
            </div>
            <form action="{{ route('admin.sample-tests.assignment', $test) }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="laboran_id" class="block text-sm font-semibold text-slate-700">Laboran</label>
                    <select id="laboran_id" name="laboran_id"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <option value="">— Belum ditugaskan —</option>
                        @foreach ($laborans as $laboran)
                            <option value="{{ $laboran->id }}" {{ $test->laboran_id === $laboran->id ? 'selected' : '' }}>
                                {{ $laboran->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('laboran_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-slate-500">
                        @if ($test->laboran)
                            Saat ini: <span class="font-semibold text-slate-700">{{ $test->laboran->name }}</span>
                        @else
                            Belum ada laboran yang ditugaskan.
                        @endif
                    </p>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Simpan Penugasan
                </button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Ubah Status</h2>
            </div>
            <form action="{{ route('admin.sample-tests.status', $test) }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PATCH')

                <div class="space-y-2">
                    @foreach ([
                        'approved' => 'Setujui',
                        'received' => 'Tandai Sampel Diterima',
                        'testing' => 'Tandai Sedang Diuji',
                        'done' => 'Tandai Selesai',
                        'rejected' => 'Tolak',
                    ] as $value => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 transition hover:border-emerald-300">
                            <input type="radio" name="status" value="{{ $value }}"
                                   {{ $test->status === $value ? 'checked' : '' }}
                                   class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('status')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div>
                    <label for="admin_notes" class="block text-sm font-semibold text-slate-700">Catatan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="admin_notes" name="admin_notes" rows="3" maxlength="2000"
                              placeholder="Alasan penolakan, keterangan, dll..."
                              class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('admin_notes', $test->result_notes) }}</textarea>
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
                    <span class="font-bold text-slate-900">{{ $test->formatted_total_cost }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Status</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $test->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $test->payment_status_label }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">No. Invoice</span>
                    <span class="font-semibold text-slate-900">{{ $test->invoice_number ?? '-' }}</span>
                </div>
                @if ($test->invoice_number)
                    <a href="{{ route('admin.sample-tests.invoice', $test) }}" target="_blank"
                       class="inline-block rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                        Lihat Invoice
                    </a>
                @endif
            </div>
            <form action="{{ route('admin.sample-tests.payment', $test) }}" method="POST" class="flex gap-2 border-t border-slate-100 px-6 py-4">
                @csrf
                @method('PATCH')
                @if (! $test->is_paid)
                    <button type="submit" name="payment_status" value="paid"
                            class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                        Tandai Lunas
                    </button>
                @endif
                @if ($test->is_paid)
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
