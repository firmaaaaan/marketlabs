@extends('layouts.admin')

@section('title', 'Tambah Laboratorium - MarketLabs')

@section('page', 'Tambah Laboratorium')

@section('content')

<a href="{{ route('admin.laboratoriums.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
    ← Kembali ke Daftar Laboratorium
</a>

<h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">Tambah Laboratorium</h1>

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

<form action="{{ route('admin.laboratoriums.store') }}" method="POST" class="mt-8 max-w-2xl space-y-4">
    @csrf

    <div>
        <label for="name" class="block text-sm font-semibold text-slate-700">Nama Laboratorium <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div>
        <label for="code" class="block text-sm font-semibold text-slate-700">Kode <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
        <input type="text" id="code" name="code" value="{{ old('code') }}" maxlength="50"
               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
        <textarea id="description" name="description" rows="4" maxlength="2000"
                  class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('description') }}</textarea>
    </div>

    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3.5">
        <span class="text-sm font-semibold text-slate-700">Aktif</span>
        <input type="checkbox" name="is_active" value="1" checked class="peer sr-only">
        <span class="relative h-6 w-11 flex-none rounded-full bg-slate-300 transition after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:bg-emerald-600 peer-checked:after:translate-x-5"></span>
    </label>

    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('admin.laboratoriums.index') }}"
           class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
            Batal
        </a>
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Simpan
        </button>
    </div>
</form>

@endsection
