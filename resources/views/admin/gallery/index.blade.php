@extends('layouts.admin')

@section('title', 'Kelola Gallery - MarketLabs')

@section('page', 'Kelola Gallery')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola Gallery</h1>
        <p class="mt-1 text-sm text-slate-600">
            Gambar gallery tampil di section "Galeri Kegiatan" pada landing page.
            Gambar nonaktif tidak akan ditampilkan di halaman publik.
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

{{-- Upload gambar --}}
<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-bold text-slate-900">Upload Gambar Gallery</h2>
    <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="new-title" class="block text-sm font-semibold text-slate-700">Judul <span class="font-normal text-slate-400">(opsional)</span></label>
                <input type="text" id="new-title" name="title" value="{{ old('title') }}" placeholder="Contoh: Pengujian Sampel Air"
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="new-caption" class="block text-sm font-semibold text-slate-700">Caption <span class="font-normal text-slate-400">(opsional)</span></label>
                <input type="text" id="new-caption" name="caption" value="{{ old('caption') }}" placeholder="Deskripsi singkat gambar"
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
        </div>
        <div class="mt-4">
            <label for="new-images" class="block text-sm font-semibold text-slate-700">Pilih Gambar <span class="text-red-500">*</span></label>
            <input type="file" id="new-images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WEBP. Maks 2 MB per gambar. Bisa pilih beberapa sekaligus.</p>
        </div>
        <div id="upload-preview" class="mt-3 grid grid-cols-4 gap-3 sm:grid-cols-6"></div>
        <div class="mt-4 flex justify-end">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                + Upload Gambar
            </button>
        </div>
    </form>
</div>

{{-- Daftar gambar --}}
<div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
        <h2 class="text-base font-bold text-slate-900">Semua Gambar ({{ $images->count() }})</h2>
        <p class="text-xs text-slate-500">Seret untuk mengubah urutan</p>
    </div>

    @if ($images->isNotEmpty())
        <div id="gallery-grid" class="grid grid-cols-2 gap-4 p-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            @foreach ($images as $image)
                <div class="gallery-item group relative overflow-hidden rounded-xl border border-slate-200 transition hover:shadow-md" data-id="{{ $image->id }}" draggable="true">
                    <div class="aspect-square overflow-hidden bg-slate-100">
                        <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->title ?? '' }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 transition group-hover:opacity-100">
                        <div class="p-3">
                            @if ($image->title)
                                <p class="text-xs font-semibold text-white line-clamp-1">{{ $image->title }}</p>
                            @endif
                            <div class="mt-1.5 flex items-center gap-1.5">
                                <button type="button" onclick="toggleEditImage({{ $image->id }})"
                                        class="rounded bg-white/20 px-2 py-1 text-[10px] font-semibold text-white backdrop-blur-sm transition hover:bg-white/30">
                                    Edit
                                </button>
                                <form action="{{ route('admin.gallery.destroy', $image) }}" method="POST" class="inline"
                                      data-confirm="Hapus gambar ini?" data-confirm-accept="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded bg-red-500/80 px-2 py-1 text-[10px] font-semibold text-white backdrop-blur-sm transition hover:bg-red-600">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @if (! $image->is_active)
                        <span class="absolute top-2 left-2 rounded bg-slate-800/70 px-1.5 py-0.5 text-[10px] font-bold text-white">Nonaktif</span>
                    @endif
                    <span class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-slate-800/60 text-[10px] font-bold text-white">
                        {{ $loop->index + 1 }}
                    </span>
                </div>

                {{-- Edit inline --}}
                <div id="edit-image-{{ $image->id }}" class="hidden col-span-full rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <form action="{{ route('admin.gallery.update', $image) }}" method="POST" class="flex flex-wrap items-end gap-3">
                        @csrf
                        @method('PUT')
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-semibold text-slate-500">Judul</label>
                            <input type="text" name="title" value="{{ $image->title }}" placeholder="Judul gambar"
                                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-semibold text-slate-500">Caption</label>
                            <input type="text" name="caption" value="{{ $image->caption }}" placeholder="Caption singkat"
                                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        </div>
                        <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_active" value="1" {{ $image->is_active ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            Aktif
                        </label>
                        <div class="flex gap-2">
                            <button type="button" onclick="toggleEditImage({{ $image->id }})"
                                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                                Batal
                            </button>
                            <button type="submit"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <div class="px-6 py-16 text-center">
            <svg class="mx-auto h-14 w-14 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 001.5-1.5V5.25a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5v14.25a1.5 1.5 0 001.5 1.5z" />
            </svg>
            <p class="mt-4 text-base font-semibold text-slate-700">Belum ada gambar</p>
            <p class="mt-1 text-sm text-slate-500">Upload gambar pertama melalui form di atas.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('new-images').addEventListener('change', function (e) {
    var preview = document.getElementById('upload-preview');
    preview.innerHTML = '';
    Array.from(e.target.files).forEach(function (file) {
        var reader = new FileReader();
        reader.onload = function (ev) {
            var div = document.createElement('div');
            div.className = 'aspect-square overflow-hidden rounded-lg border border-slate-200';
            div.innerHTML = '<img src="' + ev.target.result + '" class="h-full w-full object-cover">';
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

function toggleEditImage(id) {
    var form = document.getElementById('edit-image-' + id);
    if (form) form.classList.toggle('hidden');
}

(function () {
    var grid = document.getElementById('gallery-grid');
    if (!grid) return;
    var items = Array.from(grid.querySelectorAll('.gallery-item'));
    var dragged = null;

    items.forEach(function (item) {
        item.addEventListener('dragstart', function () {
            dragged = this;
            this.classList.add('opacity-50');
        });
        item.addEventListener('dragend', function () {
            this.classList.remove('opacity-50');
            dragged = null;
            saveOrder();
        });
        item.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (dragged && dragged !== this) {
                var rect = this.getBoundingClientRect();
                var mid = rect.left + rect.width / 2;
                if (e.clientX < mid) {
                    grid.insertBefore(dragged, this);
                } else {
                    grid.insertBefore(dragged, this.nextSibling);
                }
            }
        });
    });

    function saveOrder() {
        var order = Array.from(grid.querySelectorAll('.gallery-item')).map(function (item) {
            return item.dataset.id;
        });
        fetch('{{ route("admin.gallery.sort") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ order: order })
        });
    }
})();
</script>
@endpush

@endsection
