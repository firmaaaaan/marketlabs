@extends('layouts.admin')

@section('title', 'Pengaturan WhatsApp - MarketLabs')

@section('page', 'Pengaturan WhatsApp')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pengaturan WhatsApp</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Atur tombol WhatsApp mengambang yang tampil di pojok kanan bawah halaman publik.
            Perubahan langsung diterapkan tanpa perlu membangun ulang aplikasi.
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

<form action="{{ route('admin.whatsapp.update') }}" method="POST" class="mt-8 max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        {{-- Aktif/nonaktif --}}
        <label class="flex cursor-pointer items-start justify-between gap-4">
            <span>
                <span class="block font-bold text-slate-900">Aktifkan tombol WhatsApp</span>
                <span class="mt-0.5 block text-sm text-slate-600">
                    Tampilkan tombol hijau mengambang di pojok kanan bawah halaman publik.
                </span>
            </span>
            <span class="relative inline-flex flex-none items-center">
                <input type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled" value="1"
                       class="peer sr-only" {{ $enabled ? 'checked' : '' }}>
                <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-emerald-600 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
            </span>
        </label>

        {{-- Nomor --}}
        <div class="mt-8">
            <label for="whatsapp_number" class="block text-sm font-semibold text-slate-700">
                Nomor WhatsApp <span class="text-red-500">*</span>
            </label>
            <input type="text" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $number) }}"
                   placeholder="6281234567890" maxlength="20" required
                   class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <p class="mt-1.5 text-xs text-slate-500">
                Format internasional tanpa tanda <code class="rounded bg-slate-100 px-1 py-0.5">+</code>, spasi, atau tanda hubung.
                Contoh: <code class="rounded bg-slate-100 px-1 py-0.5">6281234567890</code>. Awalan <code class="rounded bg-slate-100 px-1 py-0.5">0</code> otomatis diubah menjadi <code class="rounded bg-slate-100 px-1 py-0.5">62</code>.
            </p>
        </div>

        {{-- Pesan awal --}}
        <div class="mt-8">
            <label for="whatsapp_message" class="block text-sm font-semibold text-slate-700">
                Pesan awal otomatis
            </label>
            <textarea id="whatsapp_message" name="whatsapp_message" rows="4" maxlength="500"
                      class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('whatsapp_message', $message) }}</textarea>
            <p class="mt-1.5 text-xs text-slate-500">
                Pesan ini otomatis terisi di chat pengunjung saat menekan tombol. Kosongkan bila tidak ingin ada pesan awal.
            </p>
        </div>
    </div>

    {{-- Pratinjau --}}
    @php
        $previewNumber = preg_replace('/\D/', '', $number);
        if (str_starts_with($previewNumber, '0')) {
            $previewNumber = '62'.substr($previewNumber, 1);
        }
    @endphp
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
        <p class="text-sm font-bold text-emerald-800">Pratinjau tautan tombol</p>
        <p class="mt-2 break-all font-mono text-xs text-emerald-700">
            {{ $enabled && $previewNumber ? 'https://wa.me/'.$previewNumber.'?text='.urlencode($message) : '— (tombol nonaktif atau nomor kosong)' }}
        </p>
    </div>

    <div class="flex justify-end">
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Simpan Pengaturan
        </button>
    </div>
</form>

@endsection
