@extends('layouts.admin')

@section('title', 'Pengaturan Invoice - MarketLabs')

@section('page', 'Pengaturan Invoice')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pengaturan Invoice</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Atur informasi perusahaan yang tampil di kop dan footer invoice (peminjaman, riset, pengujian sampel, pemeriksaan kesehatan).
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

<form action="{{ route('admin.invoice.update') }}" method="POST" class="mt-8 max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h2 class="text-base font-bold text-slate-900">Informasi Perusahaan</h2>
        <p class="mt-1 text-sm text-slate-500">Informasi ini akan ditampilkan di bagian kop invoice.</p>

        <div class="mt-6 space-y-5">
            <div>
                <label for="company_name" class="block text-sm font-semibold text-slate-700">Nama Perusahaan <span class="text-red-500">*</span></label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company_name) }}" required maxlength="255"
                       class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div>
                <label for="company_subtitle" class="block text-sm font-semibold text-slate-700">Sub Judul <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input type="text" id="company_subtitle" name="company_subtitle" value="{{ old('company_subtitle', $company_subtitle) }}" maxlength="255"
                       placeholder="Contoh: Laboratorium Riset & Pengujian"
                       class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div>
                <label for="company_tagline" class="block text-sm font-semibold text-slate-700">Tagline / Byline <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input type="text" id="company_tagline" name="company_tagline" value="{{ old('company_tagline', $company_tagline) }}" maxlength="255"
                       placeholder="Contoh: by UPT Laboratorium Terpadu UNISA Yogyakarta"
                       class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <p class="mt-1 text-xs text-slate-500">Teks kecil di bawah nama perusahaan. Kosongkan jika tidak diperlukan.</p>
            </div>

            <div>
                <label for="company_address" class="block text-sm font-semibold text-slate-700">Alamat <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input type="text" id="company_address" name="company_address" value="{{ old('company_address', $company_address) }}" maxlength="500"
                       class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="company_phone" class="block text-sm font-semibold text-slate-700">Telepon <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="text" id="company_phone" name="company_phone" value="{{ old('company_phone', $company_phone) }}" maxlength="50"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>
                <div>
                    <label for="company_email" class="block text-sm font-semibold text-slate-700">Email <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="email" id="company_email" name="company_email" value="{{ old('company_email', $company_email) }}" maxlength="255"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>
            </div>

            <div>
                <label for="company_website" class="block text-sm font-semibold text-slate-700">Website <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input type="url" id="company_website" name="company_website" value="{{ old('company_website', $company_website) }}" maxlength="255"
                       placeholder="https://www.example.com"
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

@endsection
