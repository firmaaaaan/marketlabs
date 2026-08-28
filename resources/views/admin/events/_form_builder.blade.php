@php
    $fieldId = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    $initial = json_encode(array_values($fields ?? []), JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-slate-900">{{ $label }}</h2>
            <p class="mt-0.5 text-sm text-slate-600">Buat field sesuai kebutuhan — jenis, label, opsi, dan apakah wajib diisi.</p>
        </div>
        <div class="flex items-center gap-2">
            <select id="field-type-{{ $fieldId }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                @foreach (\App\Support\FormFields::TYPES as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
            <button type="button" onclick="FormBuilder.add('{{ $fieldId }}')"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                + Tambah Field
            </button>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" id="fields-json-{{ $fieldId }}" value='{{ $initial }}'>

    <div id="fields-list-{{ $fieldId }}" data-form-builder="{{ $fieldId }}" class="mt-5 space-y-3">
        {{-- Baris field dirender oleh JavaScript --}}
    </div>

    @error($name)
        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>

@once
@push('scripts')
<script>
    window.FormBuilder = window.FormBuilder || {};
    window.FormBuilder.rows = window.FormBuilder.rows || {};

    function slugKey(str) {
        return String(str == null ? '' : str)
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    window.FormBuilder.usedKey = function (name, excludeIdx, key) {
        return (window.FormBuilder.rows[name] || []).some(function (f, i) {
            return i !== excludeIdx && f && f.key === key;
        });
    };

    window.FormBuilder.autoKey = function (name, idx) {
        const f = window.FormBuilder.rows[name][idx];
        if (!f || f._keyManual) return;
        const base = slugKey(f.label) || slugKey(f.key);
        if (!base) {
            f.key = '';
            FormBuilder.sync(name);
            return;
        }
        let key = base;
        let n = 2;
        while (FormBuilder.usedKey(name, idx, key)) key = base + '_' + (n++);
        f.key = key;
        const input = document.getElementById('key-' + name + '-' + idx);
        if (input) input.value = key;
        FormBuilder.sync(name);
    };

    window.FormBuilder.add = function (name, field) {
        const types = ['text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox', 'file'];
        field = field || { key: '', label: '', type: 'text', options: [], required: false };
        field._keyManual = !!(field.key && field.key !== '');

        const idx = (window.FormBuilder.rows[name] = window.FormBuilder.rows[name] || []).length;
        window.FormBuilder.rows[name].push(field);

        const list = document.getElementById('fields-list-' + name);
        const row = document.createElement('div');
        row.id = 'row-' + name + '-' + idx;
        row.className = 'flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4';
        row.innerHTML =
            '<div class="w-40">' +
                '<label class="block text-xs font-semibold text-slate-500">Kunci (key)</label>' +
                '<input type="text" id="key-' + name + '-' + idx + '" value="' + escapeHtml(field.key) + '" oninput="FormBuilder.updateKey(\'' + name + '\',' + idx + ',this.value)" placeholder="otomatis dari label" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">' +
            '</div>' +
            '<div class="min-w-40 flex-1">' +
                '<label class="block text-xs font-semibold text-slate-500">Label</label>' +
                '<input type="text" value="' + escapeHtml(field.label) + '" oninput="FormBuilder.updateLabel(\'' + name + '\',' + idx + ',this.value)" placeholder="mis. NIM / NIP" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">' +
            '</div>' +
            '<div class="w-36">' +
                '<label class="block text-xs font-semibold text-slate-500">Jenis</label>' +
                '<select onchange="FormBuilder.update(\'' + name + '\',' + idx + ',\'type\',this.value)" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">' +
                types.map(function (t) { return '<option value="' + t + '"' + (t === field.type ? ' selected' : '') + '>' + t + '</option>'; }).join('') +
                '</select>' +
            '</div>' +
            '<div id="options-wrap-' + name + '-' + idx + '" class="' + (field.type === 'select' || field.type === 'radio' ? '' : 'hidden') + ' min-w-48 flex-1">' +
                '<label class="block text-xs font-semibold text-slate-500">Opsi (pisah koma)</label>' +
                '<input type="text" value="' + escapeHtml((field.options || []).join(', ')) + '" oninput="FormBuilder.updateOptions(\'' + name + '\',' + idx + ',this.value)" placeholder="A, B, C" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">' +
            '</div>' +
            '<label class="flex items-center gap-2 pb-2 text-sm font-semibold text-slate-600">' +
                '<input type="checkbox" ' + (field.required ? 'checked' : '') + ' onchange="FormBuilder.update(\'' + name + '\',' + idx + ',\'required\',this.checked)" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">' +
                'Wajib' +
            '</label>' +
            '<button type="button" onclick="FormBuilder.remove(\'' + name + '\',' + idx + ')" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-100">Hapus</button>';

        list.appendChild(row);
        FormBuilder.sync(name);
    };

    window.FormBuilder.remove = function (name, idx) {
        const row = document.getElementById('row-' + name + '-' + idx);
        if (row) row.remove();
        window.FormBuilder.rows[name][idx] = null;
        FormBuilder.sync(name);
    };

    window.FormBuilder.update = function (name, idx, key, value) {
        if (window.FormBuilder.rows[name][idx]) {
            window.FormBuilder.rows[name][idx][key] = value;
            if (key === 'type') {
                const wrap = document.getElementById('options-wrap-' + name + '-' + idx);
                if (wrap) wrap.classList.toggle('hidden', value !== 'select' && value !== 'radio');
            }
        }
        FormBuilder.sync(name);
    };

    window.FormBuilder.updateLabel = function (name, idx, value) {
        if (window.FormBuilder.rows[name][idx]) {
            window.FormBuilder.rows[name][idx].label = value;
            FormBuilder.autoKey(name, idx);
        }
        FormBuilder.sync(name);
    };

    window.FormBuilder.updateKey = function (name, idx, value) {
        if (window.FormBuilder.rows[name][idx]) {
            window.FormBuilder.rows[name][idx].key = value;
            window.FormBuilder.rows[name][idx]._keyManual = true;
        }
        FormBuilder.sync(name);
    };

    window.FormBuilder.updateOptions = function (name, idx, value) {
        if (window.FormBuilder.rows[name][idx]) {
            window.FormBuilder.rows[name][idx].options = value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        }
        FormBuilder.sync(name);
    };

    window.FormBuilder.sync = function (name) {
        const cleaned = (window.FormBuilder.rows[name] || []).filter(Boolean).map(function (f) {
            return { key: f.key, label: f.label, type: f.type, options: f.options || [], required: !!f.required };
        });
        document.getElementById('fields-json-' + name).value = JSON.stringify(cleaned);
    };

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
            .replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-form-builder]').forEach(function (el) {
            const name = el.getAttribute('data-form-builder');
            const raw = document.getElementById('fields-json-' + name).value;
            let initial = [];
            try { initial = JSON.parse(raw || '[]'); } catch (e) { initial = []; }
            if (initial.length === 0) {
                FormBuilder.add(name, { key: '', label: '', type: 'text', options: [], required: false });
            } else {
                initial.forEach(function (field, i) {
                    FormBuilder.add(name, field);
                    FormBuilder.autoKey(name, i);
                });
            }
        });
    });
</script>
@endpush
@endonce