@extends('layouts.admin')

@section('title', 'Kelola User - MarketLabs')

@section('page', 'Kelola User')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola User</h1>
        <p class="mt-1 text-sm text-slate-600">Kelola akun pengguna dan tetapkan role (admin, laboran, user).</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.users.template') }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            ⬇ Download Template
        </a>
        <button type="button" onclick="document.getElementById('import-modal').classList.remove('hidden')"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            ⬆ Import User
        </button>
        <a href="{{ route('admin.users.export') }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            ⬇ Export User
        </a>
        <a href="{{ route('admin.users.create') }}"
           class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            + Tambah User
        </a>
    </div>
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        {{ session('error') }}
    </div>
@endif

{{-- Filter --}}
<form action="{{ route('admin.users.index') }}" method="GET" class="mt-6 flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari nama atau email..."
           class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    <select name="role"
            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        <option value="">Semua Role</option>
        @foreach (\App\Models\User::roles() as $value => $label)
            <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit"
            class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
        Filter
    </button>
</form>

{{-- Tabel --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">User</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode Partisipan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">NIM/NIP</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Instansi</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($users as $user)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($user->role) {
                                'admin' => 'bg-emerald-50 text-emerald-700',
                                'laboran' => 'bg-sky-50 text-sky-700',
                                default => 'bg-slate-100 text-slate-600',
                            } }}">
                                {{ $user->role_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <code class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $user->participant_code ?? '-' }}</code>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $user->nim_nip ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $user->institution ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Edit
                                </a>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                          data-confirm="Hapus user {{ $user->name }}?" data-confirm-accept="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $users->links() }}
</div>

{{-- Import Modal --}}
<div id="import-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Import User dari Excel</h2>
            <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <p class="text-sm text-slate-600">
                Upload file Excel (.xlsx/.xls/.csv) yang sesuai dengan template.
                Kolom yang wajib diisi: <strong>Nama</strong>, <strong>Email</strong>, dan <strong>Password</strong>.
                Kolom <strong>NIM/NIK/NIP</strong> dan <strong>Role</strong> opsional (default role = User).
            </p>
            <div class="mt-4">
                <label for="import-file" class="block text-sm font-semibold text-slate-700">Pilih File <span class="text-red-500">*</span></label>
                <input type="file" id="import-file" name="file" required
                       accept=".xlsx,.xls,.csv,.txt"
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                    Import
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
