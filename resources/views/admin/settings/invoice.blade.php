@extends('layouts.admin')

@section('title', 'Pengaturan Invoice - MarketLabs')

@section('page', 'Pengaturan Invoice')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pengaturan Invoice</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Atur informasi perusahaan yang tampil di kop dan footer invoice. Pratinjau berubah secara real-time.
        </p>
    </div>
</div>

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

<div class="mt-8 grid gap-8 lg:grid-cols-2">

    {{-- Form --}}
    <form action="{{ route('admin.invoice.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-base font-bold text-slate-900">Informasi Perusahaan</h2>
            <p class="mt-1 text-sm text-slate-500">Informasi ini akan ditampilkan di bagian kop invoice.</p>

            <div class="mt-6 space-y-5">
                <div>
                    <label for="company_name" class="block text-sm font-semibold text-slate-700">Nama Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company_name) }}" required maxlength="255"
                           data-preview="company_name"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label for="company_subtitle" class="block text-sm font-semibold text-slate-700">Sub Judul <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="text" id="company_subtitle" name="company_subtitle" value="{{ old('company_subtitle', $company_subtitle) }}" maxlength="255"
                           placeholder="Contoh: Laboratorium Riset & Pengujian"
                           data-preview="company_subtitle"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label for="company_tagline" class="block text-sm font-semibold text-slate-700">Tagline / Byline <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="text" id="company_tagline" name="company_tagline" value="{{ old('company_tagline', $company_tagline) }}" maxlength="255"
                           placeholder="Contoh: by UPT Laboratorium Terpadu UNISA Yogyakarta"
                           data-preview="company_tagline"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <p class="mt-1 text-xs text-slate-500">Teks kecil di bawah nama perusahaan. Kosongkan jika tidak diperlukan.</p>
                </div>

                <div>
                    <label for="company_address" class="block text-sm font-semibold text-slate-700">Alamat <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="text" id="company_address" name="company_address" value="{{ old('company_address', $company_address) }}" maxlength="500"
                           data-preview="company_address"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="company_phone" class="block text-sm font-semibold text-slate-700">Telepon <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                        <input type="text" id="company_phone" name="company_phone" value="{{ old('company_phone', $company_phone) }}" maxlength="50"
                               data-preview="company_phone"
                               class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label for="company_email" class="block text-sm font-semibold text-slate-700">Email <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                        <input type="email" id="company_email" name="company_email" value="{{ old('company_email', $company_email) }}" maxlength="255"
                               data-preview="company_email"
                               class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>

                <div>
                    <label for="company_website" class="block text-sm font-semibold text-slate-700">Website <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="url" id="company_website" name="company_website" value="{{ old('company_website', $company_website) }}" maxlength="255"
                           placeholder="https://www.example.com"
                           data-preview="company_website"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-base font-bold text-slate-900">Footer Invoice</h2>
            <p class="mt-1 text-sm text-slate-500">Teks yang ditampilkan di bagian bawah invoice.</p>

            <div class="mt-6">
                <label for="footer_text" class="block text-sm font-semibold text-slate-700">Teks Footer</label>
                <textarea id="footer_text" name="footer_text" rows="2" maxlength="500"
                          data-preview="footer_text"
                          class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('footer_text', $footer_text) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">Kosongkan untuk menggunakan teks default.</p>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                Simpan Pengaturan
            </button>
        </div>
    </form>

    {{-- Live Preview --}}
    <div class="lg:sticky lg:top-24 lg:self-start">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Pratinjau Invoice</h2>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    Live
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Perubahan pada form akan langsung terlihat di sini.</p>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            {{-- Kop Preview --}}
            <div class="border-b border-slate-200 px-6 py-5">
                <p id="preview-company_name" class="text-xl font-extrabold tracking-tight text-emerald-700">{{ $company_name }}</p>
                <p id="preview-company_tagline" class="text-xs font-semibold text-slate-500 @if(!$company_tagline) hidden @endif">{{ $company_tagline }}</p>
                <p id="preview-company_subtitle" class="text-sm text-slate-500 @if(!$company_subtitle) hidden @endif">{{ $company_subtitle }}</p>
                <p id="preview-company_address" class="mt-1 text-xs text-slate-400 @if(!$company_address) hidden @endif">{{ $company_address }}</p>
                <div id="preview-contact-row" class="mt-1 flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-slate-400 @if(!$company_phone && !$company_email && !$company_website) hidden @endif">
                    <span id="preview-company_phone" @if(!$company_phone) class="hidden" @endif>{{ $company_phone }}</span>
                    <span id="preview-company_email" @if(!$company_email) class="hidden" @endif>{{ $company_email }}</span>
                    <span id="preview-company_website" @if(!$company_website) class="hidden" @endif>{{ $company_website }}</span>
                </div>
            </div>

            {{-- Invoice Meta --}}
            <div class="flex items-start justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ditagihkan kepada</p>
                    <p class="mt-1 font-bold text-slate-900">Ahmad Fauzi</p>
                    <p class="text-sm text-slate-600">ahmad@example.com</p>
                    <p class="text-xs text-slate-500">NIM: 2024001</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Invoice</p>
                    <p class="mt-1 text-base font-extrabold text-slate-900">INV-20260903-A1B2C</p>
                    <p class="mt-1 text-xs text-slate-500">03 Sep 2026, 10:00</p>
                    <span class="mt-2 inline-block rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700">Belum Dibayar</span>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="px-6 py-4">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th class="pb-2">Item</th>
                            <th class="pb-2 text-right">Qty</th>
                            <th class="pb-2 text-right">Harga/hari</th>
                            <th class="pb-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-slate-100">
                            <td class="py-2.5">
                                <p class="font-medium text-slate-900">Mikroskop Digital</p>
                                <p class="text-xs text-slate-500">MKS-001</p>
                            </td>
                            <td class="py-2.5 text-right text-slate-600">3</td>
                            <td class="py-2.5 text-right text-slate-600">Rp 50.000</td>
                            <td class="py-2.5 text-right font-medium text-slate-900">Rp 150.000</td>
                        </tr>
                        <tr>
                            <td class="py-2.5">
                                <p class="font-medium text-slate-900">Sentrifuge Portable</p>
                                <p class="text-xs text-slate-500">SNF-042</p>
                            </td>
                            <td class="py-2.5 text-right text-slate-600">2</td>
                            <td class="py-2.5 text-right text-slate-600">Rp 35.000</td>
                            <td class="py-2.5 text-right font-medium text-slate-900">Rp 70.000</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Total --}}
                <div class="mt-4 flex justify-end">
                    <div class="w-48 space-y-1">
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span>Rp 220.000</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Diskon</span>
                            <span class="text-red-500">- Rp 0</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold text-slate-900">
                            <span>Total</span>
                            <span class="text-emerald-700">Rp 220.000</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                <p id="preview-footer_text" class="text-center text-xs text-slate-500">{{ $footer_text }}</p>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fields = {
        company_name: document.getElementById('company_name'),
        company_subtitle: document.getElementById('company_subtitle'),
        company_tagline: document.getElementById('company_tagline'),
        company_address: document.getElementById('company_address'),
        company_phone: document.getElementById('company_phone'),
        company_email: document.getElementById('company_email'),
        company_website: document.getElementById('company_website'),
        footer_text: document.getElementById('footer_text'),
    };

    const preview = {
        company_name: document.getElementById('preview-company_name'),
        company_subtitle: document.getElementById('preview-company_subtitle'),
        company_tagline: document.getElementById('preview-company_tagline'),
        company_address: document.getElementById('preview-company_address'),
        company_phone: document.getElementById('preview-company_phone'),
        company_email: document.getElementById('preview-company_email'),
        company_website: document.getElementById('preview-company_website'),
        footer_text: document.getElementById('preview-footer_text'),
    };

    const contactRow = document.getElementById('preview-contact-row');

    const defaults = {
        company_name: 'MarketLabs',
        company_subtitle: 'Laboratorium Riset & Pengujian',
        company_tagline: '',
        company_address: 'Jln. Teknologi No. 1, Kota Sains',
        company_phone: '',
        company_email: '',
        company_website: '',
        footer_text: 'Terima kasih telah menggunakan layanan MarketLabs. Invoice ini sah tanpa tanda tangan.',
    };

    function updatePreview(key) {
        const value = fields[key].value.trim();
        const display = value || defaults[key];
        const el = preview[key];

        if (!el) return;

        if (key === 'footer_text') {
            el.textContent = display;
            return;
        }

        if (key === 'company_name') {
            el.textContent = display;
            return;
        }

        el.textContent = display;

        if (key === 'company_tagline') {
            el.classList.toggle('hidden', !value);
        } else if (key === 'company_subtitle') {
            el.classList.toggle('hidden', !value);
        } else if (key === 'company_address') {
            el.classList.toggle('hidden', !value);
        } else if (['company_phone', 'company_email', 'company_website'].includes(key)) {
            el.classList.toggle('hidden', !value);
            const anyContact = fields.company_phone.value.trim() || fields.company_email.value.trim() || fields.company_website.value.trim();
            contactRow.classList.toggle('hidden', !anyContact);
        }
    }

    Object.keys(fields).forEach(function (key) {
        if (fields[key]) {
            fields[key].addEventListener('input', function () {
                updatePreview(key);
            });
        }
    });
});
</script>

@endsection
