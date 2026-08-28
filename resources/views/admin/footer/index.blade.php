@extends('layouts.admin')

@section('title', 'Pengaturan Footer - MarketLabs')

@section('page', 'Pengaturan Footer')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pengaturan Footer</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Atur alamat, kontak, dan logo yang tampil pada footer halaman publik.
            Logo muncul berderet di atas alamat; logo nonaktif tidak ditampilkan.
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

{{-- Alamat & Kontak --}}
<form action="{{ route('admin.footer.settings-update') }}" method="POST"
      class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @method('PUT')

    <h2 class="text-base font-bold text-slate-900">Alamat & Kontak</h2>
    <p class="mt-1 text-sm text-slate-500">Informasi ini tampil di kolom brand footer pada semua halaman publik.</p>

    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="footer_address" class="block text-sm font-semibold text-slate-700">Alamat <span class="text-red-500">*</span></label>
            <textarea id="footer_address" name="footer_address" rows="3" maxlength="500" required
                      class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('footer_address', $address) }}</textarea>
            <p class="mt-1.5 text-xs text-slate-500">Gunakan baris baru untuk memisahkan baris alamat (mis. nama jalan dan kota).</p>
        </div>
        <div>
            <label for="footer_phone" class="block text-sm font-semibold text-slate-700">Telepon</label>
            <input type="text" id="footer_phone" name="footer_phone" value="{{ old('footer_phone', $phone) }}"
                   placeholder="+62 812-3456-7890" maxlength="20"
                   class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div>
            <label for="footer_email" class="block text-sm font-semibold text-slate-700">Email</label>
            <input type="email" id="footer_email" name="footer_email" value="{{ old('footer_email', $email) }}"
                   placeholder="info@marketlabs.id" maxlength="255"
                   class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit"
                class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            Simpan Alamat & Kontak
        </button>
    </div>
</form>

{{-- Logo Footer --}}
<div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <h2 class="text-base font-bold text-slate-900">Logo Footer</h2>
    <p class="mt-1 text-sm text-slate-500">
        Unggah logo instansi/partner. Logo tampil berderet di atas alamat footer; urutan diatur lewat tombol naik/turun.
    </p>

    <form action="{{ route('admin.footer.logo-store') }}" method="POST" enctype="multipart/form-data"
          class="mt-6 grid gap-4 sm:grid-cols-2">
        @csrf
        <div>
            <label for="new-name" class="block text-sm font-semibold text-slate-700">Nama Logo <span class="text-red-500">*</span></label>
            <input type="text" id="new-name" name="name" value="{{ old('name') }}" required placeholder="Contoh: UPT Laboratorium Terpadu"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div>
            <label for="new-url" class="block text-sm font-semibold text-slate-700">Tautan (opsional)</label>
            <input type="url" id="new-url" name="url" value="{{ old('url') }}" placeholder="https://example.com" maxlength="255"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div>
            <label for="new-image" class="block text-sm font-semibold text-slate-700">Gambar Logo <span class="text-red-500">*</span></label>
            <input type="file" id="new-image" name="image" accept="image/png,image/jpeg,image/webp" required
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <p class="mt-1.5 text-xs text-slate-500">PNG, JPG, atau WEBP, maksimal 2 MB. Disarankan latar transparan.</p>
        </div>
        <div class="flex flex-col justify-end">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" checked
                       class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Aktif
            </label>
        </div>
        <div class="flex items-end justify-end sm:col-span-2">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                + Tambah Logo
            </button>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Logo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tautan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($logos as $logo)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <img src="{{ $logo->image_url }}" alt="{{ $logo->name }}"
                                     class="h-10 w-auto rounded-md border border-slate-200 bg-white object-contain px-1 py-1">
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $logo->name }}</p>
                            </td>
                            <td class="max-w-xs px-6 py-4">
                                @if ($logo->url)
                                    <a href="{{ $logo->url }}" target="_blank" rel="noopener"
                                       class="truncate text-xs text-emerald-600 transition hover:text-emerald-700">{{ $logo->url }}</a>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $logo->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $logo->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form action="{{ route('admin.footer.logo-move', ['logo' => $logo, 'direction' => 'up']) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600"
                                                title="Naikkan">▲</button>
                                    </form>
                                    <form action="{{ route('admin.footer.logo-move', ['logo' => $logo, 'direction' => 'down']) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600"
                                                title="Turunkan">▼</button>
                                    </form>
                                    <button type="button" onclick="toggleEdit({{ $logo->id }})"
                                            class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.footer.logo-destroy', $logo) }}" method="POST"
                                          data-confirm="Hapus logo {{ $logo->name }}?" data-confirm-accept="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="edit-form-{{ $logo->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                            <td colspan="5" class="px-6 py-4">
                                <form action="{{ route('admin.footer.logo-update', $logo) }}" method="POST" enctype="multipart/form-data"
                                      class="grid gap-4 sm:grid-cols-2">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Nama Logo</label>
                                        <input type="text" name="name" value="{{ $logo->name }}" required
                                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Tautan (opsional)</label>
                                        <input type="url" name="url" value="{{ $logo->url }}" maxlength="255"
                                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Ganti Gambar (opsional)</label>
                                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp"
                                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                        <p class="mt-1 text-[11px] text-slate-500">Kosongkan bila tidak mengganti gambar.</p>
                                    </div>
                                    <div class="flex items-end">
                                        <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                                            <input type="checkbox" name="is_active" value="1" {{ $logo->is_active ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            Aktif
                                        </label>
                                    </div>
                                    <div class="flex items-end justify-end gap-2 sm:col-span-2">
                                        <button type="button" onclick="toggleEdit({{ $logo->id }})"
                                                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                                            Batal
                                        </button>
                                        <button type="submit"
                                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                Belum ada logo. Tambahkan logo pertama melalui form di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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