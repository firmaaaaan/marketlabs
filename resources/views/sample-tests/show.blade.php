@php $isOwner = $test->user_id === auth()->id(); @endphp

@extends($isOwner ? 'layouts.app' : 'layouts.staff')

@section('title', $test->code . ' - Pengujian Sampel')
@section('page', 'Detail Pengujian Sampel')

@section('content')

<section class="{{ $isOwner ? 'py-16' : 'py-2' }}">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <a href="{{ $isOwner ? route('sample-tests.index') : route('laboran.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
            ← Kembali{{ $isOwner ? ' ke Riwayat Pengujian' : ' ke Halaman Laboran' }}
        </a>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
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
            </div>
            @if ($isOwner && in_array($test->status, ['pending', 'approved']))
                <form action="{{ route('sample-tests.cancel', $test) }}" method="POST"
                      data-confirm="Batalkan pengujian {{ $test->code }}?" data-confirm-accept="Ya, Batalkan">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-lg border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                        Batalkan Pengujian
                    </button>
                </form>
            @endif
        </div>

        <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">Detail Pengujian Sampel</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $test->items->count() }} sampel · {{ $test->services_count }} layanan · {{ $test->units_label }}</p>

        @if ($isOwner && $test->status === 'approved')
            <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50 px-5 py-4">
                <p class="text-sm font-bold text-sky-800">Pengujian Anda telah disetujui 🎉</p>
                <p class="mt-1 text-sm text-sky-700">
                    Silakan antarkan sampel ke laboratorium {{ $test->delivery_method === 'direct' ? 'sesuai jadwal' : '(dikirim melalui jasa paket)' }}.
                    Setelah sampel diterima, tim kami akan segera memproses pengujian.
                </p>
            </div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            {{-- Kiri: layanan & sampel --}}
            <div class="space-y-6 lg:col-span-2">
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
                        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
                            <p class="text-sm text-slate-500">Tidak ada sampel pada pengujian ini.</p>
                        </div>
                    @endforelse
                </div>

                <div class="flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4">
                    <span class="text-sm font-bold text-slate-900">Total Keseluruhan</span>
                    <span class="text-xl font-extrabold text-emerald-700">{{ $test->formatted_total_cost }}</span>
                </div>

                @if ($test->notes)
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-bold text-slate-900">Catatan</h2>
                        </div>
                        <p class="whitespace-pre-line px-6 py-5 text-sm font-medium text-slate-900">{{ $test->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Kanan: pengiriman, hasil & pembayaran --}}
            <div class="space-y-6">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-bold text-slate-900">Pengiriman Sampel</h2>
                    </div>
                    <div class="space-y-3 px-6 py-6 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Metode</span>
                            <span class="font-semibold text-slate-900">{{ $test->delivery_method_label }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Pemohon</span>
                            <span class="font-semibold text-slate-900">{{ $test->user->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">NIM / NIP</span>
                            <span class="font-semibold text-slate-900">{{ $test->user->nim_nip ?: '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Instansi</span>
                            <span class="font-semibold text-slate-900">{{ $test->user->institution ?: '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Laboran</span>
                            <span class="font-semibold text-slate-900">{{ $test->laboran?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-bold text-slate-900">Hasil Pengujian</h2>
                    </div>
                    <div class="space-y-3 px-6 py-6 text-sm">
                        @if ($test->result)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hasil</p>
                                <p class="mt-1 text-lg font-extrabold text-emerald-700">{{ $test->result }}</p>
                            </div>
                            @if ($test->result_notes)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Keterangan</p>
                                    <p class="mt-1 whitespace-pre-line font-medium text-slate-900">{{ $test->result_notes }}</p>
                                </div>
                            @endif
                        @elseif (! $test->result_file)
                            <p class="text-sm text-slate-500">Hasil pengujian belum tersedia.</p>
                        @endif

                        @if ($test->result_file)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dokumen Hasil</p>
                                <a href="{{ route('sample-tests.result-download', $test) }}" target="_blank"
                                   class="mt-1 inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100">
                                    📄 Lihat Dokumen Hasil
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-bold text-slate-900">Pembayaran</h2>
                    </div>
                    <div class="space-y-3 px-6 py-6 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Status Pembayaran</span>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $test->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $test->payment_status_label }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">No. Invoice</span>
                            <span class="font-semibold text-slate-900">{{ $test->invoice_number ?? '-' }}</span>
                        </div>
                        @if ($test->invoice_number && $isOwner)
                            <a href="{{ route('sample-tests.invoice', $test) }}"
                               class="inline-block rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                Lihat Invoice
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
