@extends('layouts.admin')

@section('title', 'Sertifikat Event - MarketLabs')

@section('page', 'Sertifikat Event')

@section('content')

@php
    $fonts = \App\Support\CertificateRenderer::fontFamilies();
    $defaultName = [
        'type' => 'name',
        'x' => 50,
        'y' => 60,
        'size' => 44,
        'color' => '#1e293b',
        'align' => 'center',
        'font' => 'lato',
        'weight' => 'bold',
        'enabled' => true,
    ];
    $frontLine = collect($event->certificate_layout ?? [])->first(fn ($l) => ($l['type'] ?? null) === 'name') ?: $defaultName;
    $backLine = collect($event->certificate_layout_back ?? [])->first(fn ($l) => ($l['type'] ?? null) === 'name') ?: $defaultName;
    $frontUrl = $event->certificate_template ? \Illuminate\Support\Facades\Storage::url($event->certificate_template) : null;
    $backUrl = $event->certificate_template_back ? \Illuminate\Support\Facades\Storage::url($event->certificate_template_back) : null;
@endphp

<style>
    @foreach (\App\Support\CertificateRenderer::FONTS as $family => $files)
        @foreach ($files as $weight => $file)
            @font-face {
                font-family: '{{ $family }}';
                font-weight: {{ $weight === 'bold' ? 700 : 400 }};
                font-display: swap;
                src: url('{{ asset('fonts/'.$file) }}') format('truetype');
            }
        @endforeach
    @endforeach

    .cert-name-drag {
        cursor: move;
        touch-action: none;
        text-shadow: 0 0 2px rgba(255, 255, 255, 0.9);
    }
    .cert-name-drag:hover {
        outline: 2px dashed rgba(16, 185, 129, 0.7);
        outline-offset: 2px;
    }
</style>

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Tata Letak Sertifikat</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $event->title }} · Atur posisi nama peserta di atas template. Seret langsung pada pratinjau untuk menggeser.</p>
    </div>
    <div class="flex items-center gap-3">
        <form id="back-delete-wrap" class="hidden"
              action="{{ route('admin.events.certificate.back-delete', $event) }}" method="POST"
              onsubmit="return confirm('Hapus template & pengaturan sisi belakang sertifikat?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 transition hover:bg-red-100">
                Hapus Sisi Belakang
            </button>
        </form>
        <a href="{{ route('admin.events.show', $event) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
            ← Kembali ke Event
        </a>
    </div>
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
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

<form method="POST" action="{{ route('admin.events.certificate.save', $event) }}" enctype="multipart/form-data" class="mt-8">
    @csrf

    <input type="hidden" name="certificate_layout" id="certificate-layout" value="{{ json_encode([$frontLine], JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT) }}">
    <input type="hidden" name="certificate_layout_back" id="certificate-layout-back" value="{{ json_encode([$backLine], JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT) }}">

    {{-- Toggle sisi --}}
    <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
        <button type="button" data-side="front" onclick="CertEditor.side('front')"
                class="side-tab rounded-lg px-5 py-2 text-sm font-bold text-slate-500 transition">Sisi Depan</button>
        <button type="button" data-side="back" onclick="CertEditor.side('back')"
                class="side-tab rounded-lg px-5 py-2 text-sm font-bold text-slate-500 transition">
            Sisi Belakang
            <span class="ml-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $backUrl ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                {{ $backUrl ? 'Aktif' : 'Nonaktif' }}
            </span>
        </button>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-5">
        {{-- Kiri: kontrol sisi aktif --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-slate-900">Template Master <span id="side-label"></span></h2>
                </div>
                <p class="mt-0.5 text-sm text-slate-600">Unggah gambar latar sertifikat (PNG/JPG). Disarankan resolusi besar agar hasil tajam.</p>

                <div class="mt-4">
                    <div id="template-preview-wrap" class="hidden">
                        <img id="template-preview" src="" alt="Template saat ini" class="max-h-40 w-auto rounded-lg border border-slate-200">
                        <p class="mt-2 text-xs text-slate-500">Template saat ini. Unggah file baru untuk menggantinya (opsional).</p>
                    </div>
                    <label class="mt-2 block text-sm font-semibold text-slate-600">Ganti / Unggah Template</label>
                    <input type="file" id="template-input" accept=".png,.jpg,.jpeg"
                           class="mt-1 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-xs text-slate-600 file:mr-2 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-700">
                    <p id="template-required-hint" class="mt-1 text-xs text-slate-400"></p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-slate-900">Nama Peserta</h2>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                        <input type="checkbox" id="name-enabled" onchange="CertEditor.set('enabled', this.checked)"
                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Tampilkan nama
                    </label>
                </div>
                <p class="mt-0.5 text-sm text-slate-600">Ukuran font dalam px acuan lebar 1240px. Posisi diatur dengan menyeret teks pada pratinjau.</p>

                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Posisi X (%)</label>
                        <input type="number" id="name-x" min="0" max="100" step="1" oninput="CertEditor.set('x', Number(this.value))"
                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Posisi Y (%)</label>
                        <input type="number" id="name-y" min="0" max="100" step="1" oninput="CertEditor.set('y', Number(this.value))"
                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Ukuran</label>
                        <input type="number" id="name-size" min="8" max="300" step="1" oninput="CertEditor.set('size', Number(this.value))"
                               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Warna</label>
                        <input type="color" id="name-color" oninput="CertEditor.set('color', this.value)"
                               class="mt-1 h-8 w-full cursor-pointer rounded-lg border border-slate-300 bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Rata</label>
                        <select id="name-align" onchange="CertEditor.set('align', this.value)"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900">
                            <option value="left">Kiri</option>
                            <option value="center">Tengah</option>
                            <option value="right">Kanan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Tebal</label>
                        <select id="name-weight" onchange="CertEditor.set('weight', this.value)"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900">
                            <option value="regular">Regular</option>
                            <option value="bold">Bold</option>
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <label class="block text-[11px] font-semibold text-slate-500">Font</label>
                        <select id="name-font" onchange="CertEditor.set('font', this.value)"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900">
                            @foreach ($fonts as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.events.show', $event) }}"
                   class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                    Simpan Tata Letak
                </button>
            </div>
        </div>

        {{-- Kanan: pratinjau --}}
        <div class="lg:col-span-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-slate-900">Pratinjau <span id="preview-side-label"></span></h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Seret nama pada pratinjau untuk menggeser</span>
                </div>

                <div id="cert-preview" class="relative mt-4 w-full overflow-hidden rounded-xl border border-slate-200 shadow-inner"
                     style="aspect-ratio: 1.414 / 1; background: #fff;">
                    <div id="template-empty" class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-emerald-50 via-white to-emerald-100">
                        <p class="text-sm font-semibold text-slate-400">Unggah template sisi ini untuk melihat pratinjau</p>
                    </div>
                    <img id="template-img" src="" alt="Template" class="absolute inset-0 h-full w-full object-fill">
                    <div id="preview-overlays" class="absolute inset-0"></div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Pratinjau render asli (PNG) sebelum generate --}}
<div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-slate-900">Pratinjau Render Asli</h2>
            <p class="mt-0.5 text-sm text-slate-600">Hasil PNG sebenarnya menggunakan template &amp; tata letak yang tersimpan, dengan contoh nama peserta.</p>
        </div>
        @if ($preview['front'])
            <a href="{{ \Illuminate\Support\Facades\Storage::url($preview['front']) }}" target="_blank"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                Buka PNG
            </a>
        @endif
    </div>

    @if ($preview['front'])
        <div class="mt-5 grid gap-6 md:grid-cols-2">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sisi Depan</p>
                <img src="{{ \Illuminate\Support\Facades\Storage::url($preview['front']) }}" alt="Pratinjau depan"
                     class="mt-2 w-full rounded-xl border border-slate-200">
            </div>
            @if ($preview['back'])
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sisi Belakang</p>
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($preview['back']) }}" alt="Pratinjau belakang"
                         class="mt-2 w-full rounded-xl border border-slate-200">
                </div>
            @endif
        </div>
        <p class="mt-4 rounded-lg bg-amber-50 px-4 py-2.5 text-xs font-semibold text-amber-700">
            Pratinjau diperbarui setiap halaman dibuka. Setelah mengubah template / tata letak, simpan lalu muat ulang halaman agar pratinjau mengikuti.
        </p>
    @else
        <p class="mt-4 rounded-lg bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-500">
            Simpan template master terlebih dahulu untuk melihat pratinjau render asli.
        </p>
    @endif
</div>

@push('scripts')
<script>
    const fontFamilies = {!! json_encode(array_keys($fonts), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) !!};

    const sides = {
        front: {
            template: {!! $frontUrl ? json_encode($frontUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) : 'null' !!},
            line: {!! json_encode($frontLine, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) !!}
        },
        back: {
            template: {!! $backUrl ? json_encode($backUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) : 'null' !!},
            line: {!! json_encode($backLine, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) !!}
        }
    };

    let currentSide = 'front';

    function esc(str) {
        return String(str == null ? '' : str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function clamp(v, min, max) {
        return Math.min(max, Math.max(min, v));
    }

    function syncHidden() {
        document.getElementById('certificate-layout').value = JSON.stringify([sides.front.line]);
        document.getElementById('certificate-layout-back').value = JSON.stringify([sides.back.line]);
    }

    function line() {
        return sides[currentSide].line;
    }

    window.CertEditor = {
        side: function (side) {
            if (side !== 'front' && side !== 'back') return;
            currentSide = side;
            document.querySelectorAll('.side-tab').forEach(function (btn) {
                btn.classList.toggle('text-emerald-600', btn.getAttribute('data-side') === side);
                btn.classList.toggle('bg-emerald-50', btn.getAttribute('data-side') === side);
                btn.classList.toggle('text-slate-500', btn.getAttribute('data-side') !== side);
            });
            renderControls();
            renderPreview();
        },

        set: function (key, value) {
            line()[key] = value;
            syncHidden();
            renderPreview();
        }
    };

    function renderControls() {
        const l = line();
        const isBack = currentSide === 'back';
        document.getElementById('side-label').textContent = isBack ? 'Belakang' : 'Depan';
        document.getElementById('preview-side-label').textContent = isBack ? '(Belakang)' : '(Depan)';
        document.getElementById('back-delete-wrap').classList.toggle('hidden', !sides.back.template);

        // Template
        const input = document.getElementById('template-input');
        input.name = isBack ? 'certificate_template_back' : 'certificate_template';
        input.required = isBack ? false : !sides.front.template;
        const hint = document.getElementById('template-required-hint');
        hint.textContent = isBack
            ? (sides.back.template ? 'Template belakang sudah tersedia.' : 'Opsional. Unggah untuk mengaktifkan sisi belakang.')
            : (sides.front.template ? 'Template depan sudah tersedia.' : 'Wajib diunggah.');

        const preview = document.getElementById('template-preview-wrap');
        const previewImg = document.getElementById('template-preview');
        if (sides[currentSide].template) {
            preview.classList.remove('hidden');
            previewImg.src = sides[currentSide].template;
        } else {
            preview.classList.add('hidden');
        }

        // Nama — sembunyikan untuk sisi belakang
        const nameCard = document.getElementById('name-enabled').closest('.rounded-2xl');
        if (isBack) {
            nameCard.classList.add('hidden');
            l.enabled = false;
            syncHidden();
        } else {
            nameCard.classList.remove('hidden');
            document.getElementById('name-x').value = Math.round(l.x * 10) / 10;
            document.getElementById('name-y').value = Math.round(l.y * 10) / 10;
            document.getElementById('name-size').value = l.size;
            document.getElementById('name-color').value = l.color;
            document.getElementById('name-align').value = l.align;
            document.getElementById('name-weight').value = l.weight;
            document.getElementById('name-font').value = l.font;
            document.getElementById('name-enabled').checked = !!l.enabled;
        }
    }

    function renderPreview() {
        const container = document.getElementById('cert-preview');
        const overlays = document.getElementById('preview-overlays');
        const img = document.getElementById('template-img');
        const empty = document.getElementById('template-empty');
        const hasTemplate = !!sides[currentSide].template;

        img.src = hasTemplate ? sides[currentSide].template : '';
        img.classList.toggle('hidden', !hasTemplate);
        empty.classList.toggle('hidden', hasTemplate);

        overlays.innerHTML = '';

        if (!hasTemplate) return;

        const width = container.clientWidth || 700;
        const scale = width / 1240;
        const l = line();

        // Nama (dapat diseret) — hanya sisi depan
        if (currentSide === 'front' && l.enabled) {
            const el = document.createElement('div');
            el.id = 'name-overlay';
            el.className = 'cert-name-drag';
            el.textContent = 'Nama Peserta Contoh';
            el.style.cssText = 'position:absolute;left:' + l.x + '%;top:' + l.y + '%;transform:translate(-50%,-50%);font-size:' + Math.round(l.size * scale) + 'px;color:' + l.color + ';white-space:nowrap;font-family:\'' + l.font + '\',serif;font-weight:' + (l.weight === 'bold' ? 700 : 400) + ';z-index:10;';
            if (l.align === 'left') el.style.transform = 'translateY(-50%)';
            else if (l.align === 'right') el.style.transform = 'translate(-100%,-50%)';
            attachDrag(el);
            overlays.appendChild(el);
        }


    }

    function attachDrag(el) {
        let dragging = false;

        el.addEventListener('pointerdown', function (e) {
            dragging = true;
            el.setPointerCapture(e.pointerId);
            e.preventDefault();
        });

        el.addEventListener('pointermove', function (e) {
            if (!dragging) return;
            const rect = document.getElementById('cert-preview').getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            const l = line();
            l.x = clamp(Math.round(x * 10) / 10, 0, 100);
            l.y = clamp(Math.round(y * 10) / 10, 0, 100);
            document.getElementById('name-x').value = l.x;
            document.getElementById('name-y').value = l.y;
            el.style.left = l.x + '%';
            el.style.top = l.y + '%';
            syncHidden();
        });

        el.addEventListener('pointerup', function () {
            dragging = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderControls();
        renderPreview();
    });

    window.addEventListener('resize', renderPreview);
</script>
@endpush

@endsection