@extends('layouts.admin')

@section('title', 'Edit User - MarketLabs')

@section('page', 'Edit User')

@section('content')

<a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
    ← Kembali ke Daftar User
</a>

<h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">Edit User: {{ $user->name }}</h1>

@if (session('error'))
    <div class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        {{ session('error') }}
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

<form action="{{ route('admin.users.update', $user) }}" method="POST" class="mt-8 max-w-2xl space-y-4">
    @csrf
    @method('PUT')

    <div>
        <label for="name" class="block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255"
               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold text-slate-700">Email <span class="text-xs font-normal text-slate-400">(opsional, bisa dilengkapi nanti)</span></label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" maxlength="255"
               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div>
        <label for="username" class="block text-sm font-semibold text-slate-700">Username <span class="text-red-500">*</span></label>
        <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required maxlength="255"
               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi Baru <span class="text-xs font-normal text-slate-400">(kosongkan bila tidak diubah)</span></label>
        <input type="password" id="password" name="password"
               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Konfirmasi Kata Sandi Baru</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div>
        <label for="role" class="block text-sm font-semibold text-slate-700">Role <span class="text-red-500">*</span></label>
        <select id="role" name="role" required
                class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @foreach (\App\Models\User::roles() as $value => $label)
                <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700">Kode Partisipan</label>
        <p class="mt-1.5 rounded-lg bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm font-mono font-bold text-slate-800">{{ $user->participant_code }}</p>
        <p class="mt-1 text-xs text-slate-400">Dibuat otomatis. Digunakan untuk pendaftaran event atas nama teman.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="nim_nip" class="block text-sm font-semibold text-slate-700">NIM / NIP / NIDN / NIK <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
            <input type="text" id="nim_nip" name="nim_nip" value="{{ old('nim_nip', $user->nim_nip) }}" maxlength="50"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div>
            <label for="institution" class="block text-sm font-semibold text-slate-700">Instansi <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
            <input type="text" id="institution" name="institution" value="{{ old('institution', $user->institution) }}" maxlength="255"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('admin.users.index') }}"
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
