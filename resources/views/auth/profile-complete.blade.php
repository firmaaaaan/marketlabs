@extends('layouts.auth')

@section('title', 'Lengkapi Profil')

@section('card')
    <div class="text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100">
            <svg class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900">Lengkapi Profil Anda</h1>
        <p class="mt-2 text-sm text-slate-600">Silakan lengkapi informasi akun berikut sebelum dapat mengakses fitur MarketLabs.</p>
    </div>

    @if (session('warning'))
        <div class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">
            {{ session('warning') }}
        </div>
    @endif

    <form action="{{ route('profile.complete.update') }}" method="POST" class="mt-6 space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <label for="nim_nip" class="block text-sm font-semibold text-slate-700">NIM / NIP / NIDN / NIK <span class="text-red-500">*</span></label>
            <input type="text" id="nim_nip" name="nim_nip" value="{{ old('nim_nip', Auth::user()->nim_nip) }}" required
                   placeholder="Contoh: 2101234567"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @error('nim_nip')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="institution" class="block text-sm font-semibold text-slate-700">Instansi / Universitas <span class="text-red-500">*</span></label>
            <input type="text" id="institution" name="institution" value="{{ old('institution', Auth::user()->institution) }}" required
                   placeholder="Contoh: Universitas Ahmad Dahlan"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @error('institution')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-semibold text-slate-700">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}" required
                   placeholder="Contoh: 081234567890"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @error('phone')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Simpan & Lanjutkan
        </button>
    </form>

    <p class="mt-4 text-center text-xs text-slate-400">
        Anda dapat mengubah informasi ini kapan saja melalui halaman profil.
    </p>
@endsection
