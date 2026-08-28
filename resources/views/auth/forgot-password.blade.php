@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('card')
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Lupa Kata Sandi</h1>
    <p class="mt-1 text-sm text-slate-600">Masukkan email terdaftar untuk menerima tautan reset kata sandi.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Email <span class="text-red-500">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   placeholder="Masukkan email yang terdaftar"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            Jika email terdaftar di sistem, kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Kirim Tautan Reset
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ route('login') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">&larr; Kembali ke halaman masuk</a>
    </p>
@endsection