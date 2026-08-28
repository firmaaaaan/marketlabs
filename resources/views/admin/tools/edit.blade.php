@extends('layouts.admin')

@section('title', 'Edit Alat - MarketLabs')

@section('page', 'Edit Alat')

@section('content')

<div class="mx-auto max-w-2xl">
    <nav class="text-sm text-slate-500">
        <a href="{{ route('admin.tools.index') }}" class="transition hover:text-emerald-600">Kelola Alat</a>
        <span class="mx-2">/</span>
        <span class="text-slate-700">Edit Alat</span>
    </nav>

    <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900">Edit Alat: {{ $tool->name }}</h1>

    <form action="{{ route('admin.tools.update', $tool) }}" method="POST" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="code" class="block text-sm font-semibold text-slate-700">Kode Alat <span class="font-normal text-slate-400">(otomatis)</span></label>
                <input type="text" id="code" value="{{ $tool->code }}" readonly
                       class="mt-1.5 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500">
                <p class="mt-1 text-xs text-slate-500">Kode alat dibuat otomatis oleh sistem dan tidak dapat diubah.</p>
            </div>

            <div>
                <label for="category_id" class="block text-sm font-semibold text-slate-700">Kategori <span class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" required
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">— Pilih Kategori —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $tool->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">
                    Kategori dikelola di
                    <a href="{{ route('admin.categories.index') }}" class="font-medium text-emerald-600 hover:underline">Kelola Kategori</a>.
                </p>
                @error('category_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-5">
            <label for="name" class="block text-sm font-semibold text-slate-700">Nama Alat <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $tool->name) }}" required
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Merk & Seri (opsional) --}}
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="brand" class="block text-sm font-semibold text-slate-700">Merk <span class="font-normal text-slate-400">(opsional)</span></label>
                <input type="text" id="brand" name="brand" value="{{ old('brand', $tool->brand) }}"
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                @error('brand')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="series" class="block text-sm font-semibold text-slate-700">Seri <span class="font-normal text-slate-400">(opsional)</span></label>
                <input type="text" id="series" name="series" value="{{ old('series', $tool->series) }}"
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                @error('series')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-5">
            <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></label>
            <textarea id="description" name="description" rows="4"
                      class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('description', $tool->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="total_stock" class="block text-sm font-semibold text-slate-700">Total Stok <span class="text-red-500">*</span></label>
                <input type="number" id="total_stock" name="total_stock" value="{{ old('total_stock', $tool->total_stock) }}" min="0" required
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <p class="mt-1 text-xs text-slate-500">Stok tersedia saat ini: {{ $tool->available_stock }} unit. Selisih total stok akan menyesuaikan stok tersedia.</p>
                @error('total_stock')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price_per_day" class="block text-sm font-semibold text-slate-700">Harga Sewa / Hari (Rp) <span class="text-red-500">*</span></label>
                <input type="number" id="price_per_day" name="price_per_day" value="{{ old('price_per_day', $tool->price_per_day) }}" min="0" required
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                @error('price_per_day')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Gambar (opsional) --}}
        <div class="mt-5">
            <label for="image" class="block text-sm font-semibold text-slate-700">Gambar Alat <span class="font-normal text-slate-400">(opsional)</span></label>

            @if ($tool->image)
                <div class="mt-2 flex items-center gap-4">
                    <img src="{{ asset('storage/' . $tool->image) }}" alt="{{ $tool->name }}"
                         class="h-20 w-20 rounded-lg border border-slate-200 object-cover">
                    <p class="text-xs text-slate-500">Gambar saat ini. Upload gambar baru untuk menggantinya.</p>
                </div>
            @endif

            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                   class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WEBP. Maksimal 2 MB. Kosongkan jika tidak ingin mengubah.</p>
            @error('image')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-5">
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="is_active" value="1" {{ $tool->is_active ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Aktif (dapat dipinjam)
            </label>
        </div>

        <div class="mt-8 flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.tools.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection
