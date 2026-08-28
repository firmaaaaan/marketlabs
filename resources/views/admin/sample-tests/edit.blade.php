@extends('layouts.admin')

@section('title', 'Edit Pengujian Sampel - MarketLabs')

@section('page', 'Kelola Pengujian')

@section('content')

@php
    $oldServices = old('services');

    if ($oldServices === null) {
        $oldServices = [];
        foreach ($test->items->groupBy('parameter_id') as $parameterId => $items) {
            $oldServices[$parameterId] = $items->map(fn ($item) => [
                'sample_name' => $item->sample_name,
                'sample_description' => $item->sample_description,
                'quantity' => $item->quantity,
                'sample_form_id' => $item->sample_form_id,
                'sample_type_id' => $item->sample_type_id,
            ])->all();
        }
    }
@endphp

<a href="{{ route('admin.sample-tests.show', $test) }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
    ← Kembali ke Detail Pengujian
</a>

<h1 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900">Edit Pengujian {{ $test->code }}</h1>
<p class="mt-1 text-sm text-slate-600">Ubah pemohon, layanan, dan daftar sampel.</p>

@if ($errors->any())
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <ul class="list-inside list-disc space-y-1 text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.sample-tests.update', $test) }}" method="POST" class="mt-8 space-y-6">
    @csrf
    @method('PATCH')

    {{-- Pemohon & catatan --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Informasi Pemohon</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="user_id" class="block text-sm font-semibold text-slate-700">Pemohon <span class="text-red-500">*</span></label>
                <select id="user_id" name="user_id" required
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">— Pilih Pemohon —</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $test->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="notes" class="block text-sm font-semibold text-slate-700">Catatan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <textarea id="notes" name="notes" rows="2"
                          class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('notes', $test->notes) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Pengiriman sampel --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Pengiriman Sampel <span class="text-red-500">*</span></h2>
        @error('delivery_method')
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
        @enderror
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60">
                <input type="radio" name="delivery_method" value="direct" required
                       {{ old('delivery_method', $test->delivery_method ?? 'direct') === 'direct' ? 'checked' : '' }}
                       class="mt-0.5 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <div>
                    <p class="text-sm font-bold text-slate-900">Diantar Langsung</p>
                    <p class="mt-0.5 text-xs text-slate-500">Sampel diserahkan langsung ke laboratorium.</p>
                </div>
            </label>
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60">
                <input type="radio" name="delivery_method" value="packaged" required
                       {{ old('delivery_method', $test->delivery_method) === 'packaged' ? 'checked' : '' }}
                       class="mt-0.5 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <div>
                    <p class="text-sm font-bold text-slate-900">Dipaketkan (Dikirim)</p>
                    <p class="mt-0.5 text-xs text-slate-500">Sampel dikemas dan dikirim via jasa pengiriman.</p>
                </div>
            </label>
        </div>
    </div>

    {{-- Layanan & sampel --}}
    <div class="space-y-6">
        @forelse ($parameters as $service)
            @php
                $rows = $oldServices[$service->id] ?? null;
                $checked = $rows !== null;
                if ($rows === null) {
                    $rows = [['sample_name' => '', 'sample_description' => '', 'quantity' => 1, 'sample_form_id' => '', 'sample_type_id' => '']];
                }
            @endphp
            <div class="service-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <label class="flex cursor-pointer items-center justify-between gap-3 border-b border-slate-100 px-6 py-4 transition hover:bg-slate-50">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" data-include-toggle {{ $checked ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $service->name }}</p>
                            <p class="text-xs text-slate-500">
                                @if ($service->method)
                                    Metode: {{ $service->method }} ·
                                @endif
                                Satuan: {{ $service->unit->name ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <span class="text-sm font-extrabold text-emerald-700">{{ $service->formatted_rate }}</span>
                </label>

                <div class="samples-area px-6 py-5 {{ $checked ? '' : 'hidden' }}">
                    <div class="samples-wrap space-y-4" data-service="{{ $service->id }}">
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
                                               placeholder="Contoh: Air Sungai, Serum Darah..."
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
                                                  class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ $row['sample_description'] }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="addSampleRow(this)"
                            class="mt-4 w-full rounded-lg border-2 border-dashed border-emerald-300 px-6 py-3 text-sm font-semibold text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-50">
                        + Tambah Sampel untuk Layanan Ini
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
                <p class="text-sm text-slate-500">Belum ada layanan pengujian aktif.</p>
            </div>
        @endforelse
    </div>

    <button type="submit"
            class="w-full rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
        Simpan Perubahan
    </button>
</form>

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
                        <input type="text" name="services[${serviceId}][${index}][sample_name]" required placeholder="Contoh: Air Sungai, Serum Darah..."
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
                                  class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"></textarea>
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
                input.name = input.name.replace(/services\[\d+\]\[\d+\]/, 'services[' + container.dataset.service + '][' + i + ']');
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

    document.querySelectorAll('.service-card').forEach(card => {
        const toggle = card.querySelector('[data-include-toggle]');
        const area = card.querySelector('.samples-area');
        if (!toggle || !area) return;
        toggle.addEventListener('change', () => {
            area.classList.toggle('hidden', !toggle.checked);
        });
    });
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
@endpush

@endsection
