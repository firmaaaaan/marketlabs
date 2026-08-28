@extends('layouts.admin')

@section('title', 'Kelola Kategori - MarketLabs')

@section('page', 'Kelola Kategori')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola Kategori Alat</h1>
        <p class="mt-1 text-sm text-slate-600">Kategori dipilih lewat dropdown saat menambah/mengedit alat.</p>
    </div>
    <a href="{{ route('admin.tools.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        Kembali ke Alat
    </a>
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

    {{-- Tambah kategori --}}
    <form action="{{ route('admin.categories.store') }}" method="POST" class="mt-6">
        @csrf
        <label for="new-category" class="block text-sm font-semibold text-slate-700">Nama Kategori <span class="text-red-500">*</span></label>
        <div class="mt-1.5 flex gap-3">
            <input type="text" id="new-category" name="name" value="{{ old('name') }}" required placeholder="Nama kategori baru..."
                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <button type="submit"
                    class="flex-none rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                + Tambah
            </button>
        </div>
        @error('name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </form>

    {{-- Daftar kategori --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <ul class="divide-y divide-slate-200">
            @forelse ($categories as $category)
                <li class="flex items-center justify-between gap-4 px-6 py-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ $category->name }}</p>
                        <p class="text-xs text-slate-500">{{ $category->tools_count }} alat</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                onclick="toggleEdit({{ $category->id }})"
                                class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                            Edit
                        </button>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                              data-confirm="Hapus kategori {{ $category->name }}?" data-confirm-accept="Ya, Hapus">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                Hapus
                            </button>
                        </form>
                    </div>
                </li>

                {{-- Form edit tersembunyi --}}
                <li id="edit-form-{{ $category->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50 px-6 py-4">
                    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="flex gap-3">
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $category->name }}" required
                               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <button type="submit"
                                class="flex-none rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Simpan
                        </button>
                        <button type="button" onclick="toggleEdit({{ $category->id }})"
                                class="flex-none rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                            Batal
                        </button>
                    </form>
                </li>
            @empty
                <li class="px-6 py-10 text-center text-sm text-slate-500">Belum ada kategori.</li>
            @endforelse
        </ul>
    </div>

@push('scripts')
<script>
    function toggleEdit(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form) form.classList.toggle('hidden');
    }
</script>
@endpush

@endsection
