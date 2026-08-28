@extends('layouts.app')

@section('title', 'Ajukan Pengujian Sampel - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('cart.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
            ← Kembali ke Keranjang
        </a>

        <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-slate-900">Ajukan Pengujian Sampel</h1>
        <p class="mt-2 text-sm text-slate-600">
            Untuk setiap layanan yang Anda pilih, tambahkan satu atau lebih sampel yang akan diuji.
            Satu layanan boleh digunakan untuk banyak sampel.
        </p>

        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                <ul class="list-inside list-disc space-y-1 text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $oldServices = old('services');
        @endphp

        <form action="{{ route('sample-tests.store') }}" method="POST" class="mt-8 space-y-6" id="checkout-form">
            @csrf

            {{-- Informasi pemohon --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Informasi Pemohon</h2>
                <p class="mt-0.5 text-xs text-slate-500">Data pemohon diambil dari profil Anda.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama</p>
                        <p class="mt-1 font-medium text-slate-900">{{ auth()->user()->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email</p>
                        <p class="mt-1 font-medium text-slate-900">{{ auth()->user()->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">NIM / NIP</p>
                        <p class="mt-1 font-medium text-slate-900">{{ auth()->user()->nim_nip ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Instansi</p>
                        <p class="mt-1 font-medium text-slate-900">{{ auth()->user()->institution ?: '-' }}</p>
                    </div>
                </div>
                @if (! auth()->user()->nim_nip || ! auth()->user()->institution)
                    <p class="mt-3 rounded-lg bg-amber-50 px-4 py-2.5 text-xs font-medium text-amber-700">
                        Lengkapi NIM/NIP dan instansi di
                        <a href="{{ route('profile.show') }}" class="font-bold underline">halaman profil</a>
                        agar data pengajuan lengkap.
                    </p>
                @endif
            </div>

            {{-- Daftar layanan + sampel --}}
            <div id="services-wrap" class="space-y-6">
                @foreach ($services as $service)
                    @php
                        $rows = $oldServices[$service->id] ?? [['sample_name' => '', 'sample_description' => '', 'quantity' => 1, 'sample_form_id' => '', 'sample_type_id' => '']];
                    @endphp
                    <div class="service-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-emerald-50/60 px-6 py-4">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ $service->name }}</h2>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    @if ($service->method)
                                        Metode: {{ $service->method }} ·
                                    @endif
                                    Satuan: {{ $service->unit->name ?? '-' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-extrabold text-emerald-700">{{ $service->formatted_rate }}</p>
                                <p class="text-xs text-slate-500">per {{ $service->unit->name ?? 'satuan' }}</p>
                            </div>
                        </div>

                        <div class="px-6 py-5">
                            <div class="samples-wrap space-y-4">
                                @foreach ($rows as $rowIndex => $row)
                                    <div class="sample-row rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sampel #<span class="sample-number">{{ $rowIndex + 1 }}</span></p>
                                            <button type="button" onclick="removeSampleRow(this)"
                                                    class="text-xs font-semibold text-red-500 transition hover:text-red-600 {{ count($rows) === 1 ? 'hidden' : '' }}">
                                                Hapus Sampel
                                            </button>
                                        </div>

                                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700">Nama Sampel <span class="text-red-500">*</span></label>
                                                <input type="text" name="services[{{ $service->id }}][{{ $rowIndex }}][sample_name]" value="{{ $row['sample_name'] }}" required
                                                       placeholder="Contoh: Air Sungai Citarum"
                                                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700">Jumlah <span class="text-red-500">*</span></label>
                                                <input type="number" name="services[{{ $service->id }}][{{ $rowIndex }}][quantity]" value="{{ $row['quantity'] }}" min="1" required
                                                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700">Bentuk <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                                                <select name="services[{{ $service->id }}][{{ $rowIndex }}][sample_form_id]"
                                                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                    <option value="">— Pilih Bentuk —</option>
                                                    @foreach ($forms as $form)
                                                        <option value="{{ $form->id }}" {{ (string) $row['sample_form_id'] === (string) $form->id ? 'selected' : '' }}>{{ $form->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700">Jenis <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                                                <select name="services[{{ $service->id }}][{{ $rowIndex }}][sample_type_id]"
                                                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                    <option value="">— Pilih Jenis —</option>
                                                    @foreach ($types as $type)
                                                        <option value="{{ $type->id }}" {{ (string) $row['sample_type_id'] === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-semibold text-slate-700">Deskripsi Sampel <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                                                <textarea name="services[{{ $service->id }}][{{ $rowIndex }}][sample_description]" rows="2"
                                                          placeholder="Kondisi sampel, sumber, dll."
                                                          class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ $row['sample_description'] }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" onclick="addSampleRow(this)"
                                    data-service="{{ $service->id }}"
                                    class="mt-4 w-full rounded-lg border-2 border-dashed border-emerald-300 px-6 py-3 text-sm font-semibold text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-50">
                                + Tambah Sampel untuk Layanan Ini
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Metode pengiriman sampel --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Pengiriman Sampel <span class="text-red-500">*</span></h2>
                <p class="mt-0.5 text-xs text-slate-500">Bagaimana sampel akan Anda kirimkan ke laboratorium?</p>
                @error('delivery_method')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60">
                        <input type="radio" name="delivery_method" value="direct" required
                               {{ old('delivery_method', 'direct') === 'direct' ? 'checked' : '' }}
                               class="mt-0.5 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-sm font-bold text-slate-900">Diantar Langsung</p>
                            <p class="mt-0.5 text-xs text-slate-500">Sampel diserahkan langsung ke laboratorium MarketLabs.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60">
                        <input type="radio" name="delivery_method" value="packaged" required
                               {{ old('delivery_method') === 'packaged' ? 'checked' : '' }}
                               class="mt-0.5 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-sm font-bold text-slate-900">Dipaketkan (Dikirim)</p>
                            <p class="mt-0.5 text-xs text-slate-500">Sampel dikemas dan dikirim via jasa pengiriman (kurir/ekspedisi).</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Catatan Tambahan</h2>
                <textarea name="notes" rows="3" placeholder="Permintaan khusus, target tanggal, dll. (opsional)"
                          class="mt-3 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('notes') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                Ajukan Pengujian
            </button>
        </form>
    </div>
</section>

@push('scripts')
<script>
    function sampleRowTemplate(serviceId, index, formOptions, typeOptions) {
        return `
            <div class="sample-row rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sampel #<span class="sample-number">${index}</span></p>
                    <button type="button" onclick="removeSampleRow(this)"
                            class="text-xs font-semibold text-red-500 transition hover:text-red-600">
                        Hapus Sampel
                    </button>
                </div>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Nama Sampel <span class="text-red-500">*</span></label>
                        <input type="text" name="services[${serviceId}][${index}][sample_name]" required placeholder="Contoh: Air Sungai Citarum"
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" name="services[${serviceId}][${index}][quantity]" value="1" min="1" required
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Bentuk <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                        <select name="services[${serviceId}][${index}][sample_form_id]"
                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <option value="">— Pilih Bentuk —</option>
                            ${formOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Jenis <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                        <select name="services[${serviceId}][${index}][sample_type_id]"
                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <option value="">— Pilih Jenis —</option>
                            ${typeOptions}
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700">Deskripsi Sampel <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                        <textarea name="services[${serviceId}][${index}][sample_description]" rows="2"
                                  placeholder="Kondisi sampel, sumber, dll."
                                  class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"></textarea>
                    </div>
                </div>
            </div>
        `;
    }

    function formOptionsHtml() {
        let html = '';
        document.querySelectorAll('#form-data option').forEach(opt => {
            html += '<option value="' + opt.value + '">' + opt.textContent + '</option>';
        });
        return html;
    }

    function typeOptionsHtml() {
        let html = '';
        document.querySelectorAll('#type-data option').forEach(opt => {
            html += '<option value="' + opt.value + '">' + opt.textContent + '</option>';
        });
        return html;
    }

    function renumberRows(container) {
        container.querySelectorAll('.sample-row').forEach((row, i) => {
            row.querySelector('.sample-number').textContent = i + 1;
            row.querySelectorAll('[name^="services["]').forEach(input => {
                input.name = input.name.replace(/services\[[^\]]+\]\[\d+\]/, 'services[' + container.dataset.service + '][' + i + ']');
            });
            const removeBtn = row.querySelector('button[onclick="removeSampleRow(this)"]');
            removeBtn.classList.toggle('hidden', container.querySelectorAll('.sample-row').length === 1);
        });
    }

    function addSampleRow(btn) {
        const card = btn.closest('.service-card');
        const wrap = card.querySelector('.samples-wrap');
        const index = wrap.querySelectorAll('.sample-row').length;
        wrap.insertAdjacentHTML('beforeend', sampleRowTemplate(wrap.dataset.service, index, formOptionsHtml(), typeOptionsHtml()));
        renumberRows(wrap);
    }

    function removeSampleRow(btn) {
        const row = btn.closest('.sample-row');
        const wrap = row.closest('.samples-wrap');
        if (wrap.querySelectorAll('.sample-row').length <= 1) return;
        row.remove();
        renumberRows(wrap);
    }
</script>

{{-- Data dropdown untuk template JS --}}
<select id="form-data" class="hidden" aria-hidden="true">
    @foreach ($forms as $form)
        <option value="{{ $form->id }}">{{ $form->name }}</option>
    @endforeach
</select>
<select id="type-data" class="hidden" aria-hidden="true">
    @foreach ($types as $type)
        <option value="{{ $type->id }}">{{ $type->name }}</option>
    @endforeach
</select>

<script>
    // Pasang dataset service untuk tiap wrapper sampel.
    document.querySelectorAll('.samples-wrap').forEach(wrap => {
        wrap.dataset.service = wrap.closest('.service-card').querySelector('button[data-service]').dataset.service;
    });
</script>
@endpush

@endsection
