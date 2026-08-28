@extends('layouts.admin')

@section('title', 'Kelola Menu & Landing Page - MarketLabs')
@section('page', 'Kelola Menu & Landing Page')

@section('content')

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

{{-- Logo & Favicon --}}
<div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-900">Logo &amp; Favicon</h2>
        <p class="mt-0.5 text-xs text-slate-500">Atur logo yang tampil di navbar/footer dan favicon (ikon tab browser).</p>
    </div>
    <form action="{{ route('admin.menus.branding.update') }}" method="POST" enctype="multipart/form-data" class="px-6 py-4">
        @csrf
        @method('PUT')

        <div class="grid gap-6 sm:grid-cols-2">
            {{-- Logo --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700">Logo Situs</label>
                <p class="mt-0.5 text-xs text-slate-500">PNG / JPG / WEBP / SVG, maks 2MB. Latar transparan disarankan.</p>
                <div class="mt-3 flex items-center gap-4">
                    <div class="flex h-16 w-16 flex-none items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-1">
                        @if ($logo)
                            <img src="{{ asset('storage/' . $logo) }}" alt="Logo situs" class="h-full w-full object-contain">
                        @else
                            <span class="text-2xl font-bold text-emerald-600">M</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                        <label class="mt-2 flex w-fit cursor-pointer items-center gap-1.5 text-xs text-slate-500">
                            <input type="checkbox" name="remove_logo" value="1"
                                   class="h-3.5 w-3.5 rounded border-slate-300 text-red-600 focus:ring-red-500">
                            Hapus logo
                        </label>
                    </div>
                </div>
            </div>

            {{-- Favicon --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700">Favicon</label>
                <p class="mt-0.5 text-xs text-slate-500">ICO / PNG / SVG, maks 2MB. Ukuran 32x32 disarankan.</p>
                <div class="mt-3 flex items-center gap-4">
                    <div class="flex h-16 w-16 flex-none items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-1">
                        @if ($favicon)
                            <img src="{{ asset('storage/' . $favicon) }}" alt="Favicon situs" class="h-full w-full object-contain">
                        @else
                            <span class="text-2xl font-bold text-emerald-600">F</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <input type="file" name="favicon" accept=".ico,.png,.jpg,.jpeg,.webp"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                        <label class="mt-2 flex w-fit cursor-pointer items-center gap-1.5 text-xs text-slate-500">
                            <input type="checkbox" name="remove_favicon" value="1"
                                   class="h-3.5 w-3.5 rounded border-slate-300 text-red-600 focus:ring-red-500">
                            Hapus favicon
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Simpan Logo &amp; Favicon
            </button>
        </div>
    </form>
</div>

<div class="grid gap-6 lg:grid-cols-2">

    {{-- Sidebar Menu --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-900">Sidebar Menu</h2>
            <p class="mt-0.5 text-xs text-slate-500">Atur menu yang muncul di sidebar admin. Seret untuk mengubah urutan.</p>
        </div>
        <div class="px-6 py-4">
            <div id="sidebar-items" class="space-y-2">
                @forelse ($sidebarItems as $item)
                    <div class="menu-item flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 transition hover:border-emerald-300"
                         data-id="{{ $item->id }}" draggable="true">
                        <span class="cursor-grab text-slate-400 hover:text-slate-600">⠿</span>
                        <div class="min-w-0 flex-1">
                            <input type="text" value="{{ $item->label }}" data-field="label"
                                   class="w-full rounded border-0 bg-transparent text-sm font-semibold text-slate-900 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <input type="text" value="{{ $item->route_name }}" data-field="route_name" placeholder="route_name"
                                   class="mt-1 w-full rounded border-0 bg-transparent text-xs text-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" {{ $item->is_active ? 'checked' : '' }} data-toggle
                                   class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs text-slate-500">Aktif</span>
                        </label>
                        <button type="button" data-save class="rounded bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                            Simpan
                        </button>
                        <button type="button" data-delete class="rounded bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada menu sidebar.</p>
                @endforelse
            </div>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 p-4">
                <p class="text-xs font-bold text-slate-500">Tambah Menu Sidebar</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    <input type="text" id="new-sidebar-label" placeholder="Label menu"
                           class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <input type="text" id="new-sidebar-route" placeholder="route_name (opsional)"
                           class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <button type="button" id="add-sidebar-btn"
                        class="mt-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    + Tambah Menu
                </button>
            </div>
        </div>
    </div>

    {{-- Topbar Menu --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-900">Topbar Menu</h2>
            <p class="mt-0.5 text-xs text-slate-500">Atur menu yang muncul di topbar navigation.</p>
        </div>
        <div class="px-6 py-4">
            <div id="topbar-items" class="space-y-2">
                @forelse ($topbarItems as $item)
                    <div class="menu-item flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 transition hover:border-emerald-300"
                         data-id="{{ $item->id }}" draggable="true">
                        <span class="cursor-grab text-slate-400 hover:text-slate-600">⠿</span>
                        <div class="min-w-0 flex-1">
                            <input type="text" value="{{ $item->label }}" data-field="label"
                                   class="w-full rounded border-0 bg-transparent text-sm font-semibold text-slate-900 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <input type="text" value="{{ $item->route_name }}" data-field="route_name" placeholder="route_name"
                                   class="mt-1 w-full rounded border-0 bg-transparent text-xs text-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" {{ $item->is_active ? 'checked' : '' }} data-toggle
                                   class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs text-slate-500">Aktif</span>
                        </label>
                        <button type="button" data-save class="rounded bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                            Simpan
                        </button>
                        <button type="button" data-delete class="rounded bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada menu topbar.</p>
                @endforelse
            </div>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 p-4">
                <p class="text-xs font-bold text-slate-500">Tambah Menu Topbar</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    <input type="text" id="new-topbar-label" placeholder="Label menu"
                           class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <input type="text" id="new-topbar-route" placeholder="route_name atau URL"
                           class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <button type="button" id="add-topbar-btn"
                        class="mt-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    + Tambah Menu
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Landing Page Sections --}}
<div class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-900">Landing Page Sections</h2>
        <p class="mt-0.5 text-xs text-slate-500">Atur judul, deskripsi, dan urutan section di halaman depan. Seret untuk mengubah urutan.</p>
    </div>
    <div class="px-6 py-4">
        <div id="landing-sections" class="space-y-3">
            @forelse ($sections as $section)
                <div class="section-item rounded-lg border border-slate-200 bg-white p-4 transition hover:border-emerald-300"
                     data-id="{{ $section->id }}" draggable="true">
                    <div class="flex items-center gap-3">
                        <span class="cursor-grab text-slate-400 hover:text-slate-600">⠿</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ $section->key }}</span>
                                <input type="text" value="{{ $section->title }}" data-field="title"
                                       class="flex-1 rounded border-0 bg-transparent text-sm font-semibold text-slate-900 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <input type="text" value="{{ $section->description }}" data-field="description" placeholder="Deskripsi singkat (opsional)"
                                   class="mt-1 w-full rounded border-0 bg-transparent text-xs text-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" {{ $section->is_active ? 'checked' : '' }} data-toggle
                                   class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs text-slate-500">Aktif</span>
                        </label>
                        <button type="button" data-save-section class="rounded bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                            Simpan
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada section.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var token = document.querySelector('meta[name="csrf-token"]').content;

    function apiPost(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data)
        }).then(function (r) { return r.json(); });
    }

    function apiPut(url, data) {
        return fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data)
        }).then(function (r) { return r.json(); });
    }

    function apiDelete(url) {
        return fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); });
    }

    // Save menu item
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-save]');
        if (!btn) return;
        var item = btn.closest('.menu-item');
        var id = item.dataset.id;
        var label = item.querySelector('[data-field="label"]').value;
        var route_name = item.querySelector('[data-field="route_name"]').value;
        var is_active = item.querySelector('[data-toggle]').checked;

        apiPut('/admin/menus/item/' + id, { label: label, route_name: route_name || null, is_active: is_active })
            .then(function () { location.reload(); });
    });

    // Delete menu item
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-delete]');
        if (!btn) return;
        if (!confirm('Hapus menu ini?')) return;
        var item = btn.closest('.menu-item');
        apiDelete('/admin/menus/item/' + item.dataset.id).then(function () { location.reload(); });
    });

    // Toggle menu item
    document.addEventListener('change', function (e) {
        if (!e.target.matches('[data-toggle]')) return;
        var item = e.target.closest('.menu-item, .section-item');
        if (!item) return;
        var id = item.dataset.id;
        var isSection = item.classList.contains('section-item');
        var url = isSection ? '/admin/menus/section/' + id + '/toggle' : '/admin/menus/item/' + id + '/toggle';
        apiPost(url, {}).then(function () { location.reload(); });
    });

    // Save section
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-save-section]');
        if (!btn) return;
        var item = btn.closest('.section-item');
        var id = item.dataset.id;
        var title = item.querySelector('[data-field="title"]').value;
        var description = item.querySelector('[data-field="description"]').value;

        apiPut('/admin/menus/section/' + id, { title: title, description: description || null, is_active: true })
            .then(function () { location.reload(); });
    });

    // Add sidebar menu
    document.getElementById('add-sidebar-btn').addEventListener('click', function () {
        var label = document.getElementById('new-sidebar-label').value.trim();
        var route_name = document.getElementById('new-sidebar-route').value.trim();
        if (!label) return alert('Label wajib diisi.');
        apiPost('/admin/menus/item', { group: 'sidebar', label: label, route_name: route_name || null, sort_order: 99 })
            .then(function () { location.reload(); });
    });

    // Add topbar menu
    document.getElementById('add-topbar-btn').addEventListener('click', function () {
        var label = document.getElementById('new-topbar-label').value.trim();
        var route_name = document.getElementById('new-topbar-route').value.trim();
        if (!label) return alert('Label wajib diisi.');
        apiPost('/admin/menus/item', { group: 'topbar', label: label, route_name: route_name || null, sort_order: 99 })
            .then(function () { location.reload(); });
    });

    // Drag & drop sort
    function initDrag(containerId, sortUrl) {
        var container = document.getElementById(containerId);
        if (!container) return;
        var dragItem = null;

        container.addEventListener('dragstart', function (e) {
            dragItem = e.target.closest('.menu-item, .section-item');
            if (dragItem) dragItem.classList.add('opacity-50');
        });

        container.addEventListener('dragend', function (e) {
            if (dragItem) dragItem.classList.remove('opacity-50');
            dragItem = null;
        });

        container.addEventListener('dragover', function (e) {
            e.preventDefault();
            var target = e.target.closest('.menu-item, .section-item');
            if (target && target !== dragItem) {
                var rect = target.getBoundingClientRect();
                var midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    container.insertBefore(dragItem, target);
                } else {
                    container.insertBefore(dragItem, target.nextSibling);
                }
            }
        });

        container.addEventListener('drop', function (e) {
            e.preventDefault();
            var items = container.querySelectorAll('.menu-item, .section-item');
            var ids = [];
            items.forEach(function (el) { ids.push(parseInt(el.dataset.id)); });
            apiPost(sortUrl, { items: ids });
        });
    }

    initDrag('sidebar-items', '/admin/menus/sort');
    initDrag('topbar-items', '/admin/menus/sort');
    initDrag('landing-sections', '/admin/menus/sections/sort');
})();
</script>
@endpush
