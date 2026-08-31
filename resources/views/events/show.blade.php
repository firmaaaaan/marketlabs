@extends('layouts.app')

@section('title', $event->title.' - MarketLabs')

@section('content')

<section class="bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-900 pt-32 pb-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('events.index') }}" class="text-sm font-semibold text-emerald-200 transition hover:text-white">
            ← Semua Event
        </a>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <span class="rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold text-emerald-100 backdrop-blur">
                {{ $event->code }}
            </span>
            <span class="rounded-full px-4 py-1.5 text-xs font-semibold {{ $event->status === \App\Models\Event::STATUS_ACTIVE ? 'bg-emerald-400 text-emerald-950' : 'bg-sky-400 text-sky-950' }}">
                {{ $event->status_label }}
            </span>
            @if ($event->mode)
                <span class="rounded-full px-4 py-1.5 text-xs font-semibold {{ match ($event->mode) {
                    \App\Models\Event::MODE_ONLINE => 'bg-sky-400 text-sky-950',
                    \App\Models\Event::MODE_OFFLINE => 'bg-amber-400 text-amber-950',
                    \App\Models\Event::MODE_HYBRID => 'bg-violet-400 text-violet-950',
                    default => 'bg-white/10 text-emerald-100',
                } }}">
                    {{ $event->mode_label }}
                </span>
            @endif
        </div>
        <h1 class="mt-4 max-w-3xl text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $event->title }}</h1>
        <p class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-emerald-50/85">
            @if ($event->starts_at)
                <span>{{ $event->starts_at->translatedFormat('l, d F Y · H:i') }} WIB</span>
            @endif
            @if ($event->location)
                <span>📍 {{ $event->location }}</span>
            @endif
            <span>
                {{ $event->registrations_count }} peserta
                @if ($event->quota)
                    dari {{ $event->quota }} kuota
                @endif
            </span>
        </p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                @if ($event->poster || $event->image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($event->poster ?: $event->image) }}" alt="{{ $event->title }}"
                         class="mb-6 max-h-[32rem] w-full rounded-xl object-contain bg-slate-100">
                @endif
                <h2 class="text-xl font-extrabold text-slate-900">Tentang Event</h2>
                <div class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-600">
                    {{ $event->description ?: 'Belum ada deskripsi.' }}
                </div>
            </div>

            @if (auth()->check() && $alreadyRegistered)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-6">
                    <h2 class="text-lg font-bold text-slate-900">Anda sudah terdaftar 🎉</h2>
                    <p class="mt-1 text-sm text-slate-600">Kelola presensi dan sertifikat Anda melalui halaman Event Saya.</p>
                    <a href="{{ route('events.my') }}" class="mt-4 inline-block rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Buka Event Saya
                    </a>
                </div>
            @endif
        </div>

        {{-- Kolom kanan: form pendaftaran / status --}}
        <div class="lg:col-span-2">
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Biaya Pendaftaran</p>
                @if ($event->has_fee)
                    @if ($event->has_discount)
                        <p class="mt-2 text-sm text-slate-400 line-through">{{ $event->fee_label }}</p>
                        <p class="text-2xl font-extrabold text-emerald-700">{{ $event->effective_fee_label }}</p>
                        <p class="mt-1 text-xs font-semibold text-red-600">
                            Hemat {{ $event->discount_label }}
                            @if ($event->discount_percent !== null)
                                ({{ $event->discount_percent }}%)
                            @endif
                        </p>
                    @else
                        <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $event->fee_label }}</p>
                    @endif
                @else
                    <p class="mt-2 text-2xl font-extrabold text-emerald-600">Gratis</p>
                @endif
            </div>

            @auth
                @if (! $alreadyRegistered && $event->is_open)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                        <h2 class="text-lg font-extrabold text-slate-900">Formulir Pendaftaran</h2>
                        <p class="mt-1 text-sm text-slate-500">Lengkapi data berikut untuk mendaftar.</p>

                        @if ($errors->any())
                            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                                <p class="text-sm font-bold text-red-700">Periksa kembali isian Anda:</p>
                                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-600">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-5 grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1 text-sm font-semibold">
                            <button type="button" data-reg-mode="self" class="reg-mode-btn rounded-lg bg-white px-4 py-2 text-slate-900 shadow-sm">
                                Diri Sendiri
                            </button>
                            <button type="button" data-reg-mode="friend" class="reg-mode-btn rounded-lg px-4 py-2 text-slate-500 transition hover:text-slate-700">
                                Daftarkan Teman
                            </button>
                        </div>

                        <div id="friend-panel" class="hidden space-y-4 rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Kode Partisipan Teman</label>
                                <p class="mt-0.5 text-xs text-slate-500">Minta kode dari teman Anda (terlihat di halaman profilnya). Anda bisa mendaftarkan beberapa teman sekaligus.</p>
                                <div class="mt-1.5 flex gap-2">
                                    <input type="text" id="friend-code-input" placeholder="ML-XXXXXXXX" autocomplete="off"
                                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    <button type="button" id="friend-search-btn"
                                            class="shrink-0 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        + Tambah
                                    </button>
                                </div>
                            </div>
                            <div id="friend-error" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700"></div>
                            <div id="friend-list" class="space-y-2">
                                {{-- Daftar teman yang berhasil ditambahkan muncul di sini via JS --}}
                            </div>
                            <p id="friend-empty-hint" class="text-xs text-slate-500 italic">
                                Belum ada teman ditambahkan. Masukkan kode dan klik <strong>+ Tambah</strong>.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('events.store', $event) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div id="friend-codes-container"></div>
                            <div id="friend-answers-container"></div>
                            <input type="hidden" name="register_for" id="register-for-field" value="self">
                            @if (! $alreadyRegistered)
                                <label id="register-self-label" class="hidden items-center gap-2.5 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" name="register_self" value="1"
                                           class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    Daftarkan diri saya juga
                                </label>
                            @endif
                            <div id="main-form-fields">
                                @include('events._fields', ['fields' => \App\Support\FormFields::normalize($event->form_fields)])
                            </div>

                            <button type="submit"
                                    class="mt-2 w-full rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                                Daftar Sekarang
                            </button>
                        </form>
                    </div>
                @elseif (! $alreadyRegistered && ! $event->is_open)
                    <div class="rounded-2xl border border-slate-200 bg-white p-7 text-center shadow-sm">
                        <p class="text-lg font-bold text-slate-800">Pendaftaran Ditutup</p>
                        <p class="mt-1 text-sm text-slate-500">Kuota penuh atau melewati tenggat pendaftaran.</p>
                    </div>
                @endif
            @else
                <div class="rounded-2xl border border-slate-200 bg-white p-7 text-center shadow-sm">
                    <p class="text-lg font-bold text-slate-800">Masuk untuk mendaftar</p>
                    <p class="mt-1 text-sm text-slate-500">Pendaftaran event memerlukan akun MarketLabs.</p>
                    <a href="{{ route('login') }}"
                       class="mt-4 inline-block rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Masuk / Daftar Akun
                    </a>
                </div>
            @endauth
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    var searchUrl = @json(route('events.search-friend', $event));
    var MAX_FRIENDS = {{ \App\Http\Controllers\EventController::MAX_PROXY_PER_EVENT }};
    var friendFields = @json(collect(\App\Support\FormFields::normalize($event->form_fields))->filter(fn ($f) => in_array($f['type'], ['select', 'radio']))->values());
    var friendCodes = [];
    var friendEntries = {};

    function setMode(mode) {
        var isFriend = mode === 'friend';
        document.querySelectorAll('.reg-mode-btn').forEach(function (btn) {
            var active = btn.dataset.regMode === mode;
            btn.classList.toggle('bg-white', active);
            btn.classList.toggle('text-slate-900', active);
            btn.classList.toggle('shadow-sm', active);
            btn.classList.toggle('text-slate-500', !active);
        });
        document.getElementById('register-for-field').value = isFriend ? 'friend' : 'self';
        document.getElementById('friend-panel').classList.toggle('hidden', !isFriend);

        var selfLabel = document.getElementById('register-self-label');
        if (selfLabel) {
            selfLabel.classList.toggle('hidden', !isFriend);
            selfLabel.classList.toggle('flex', isFriend);
            selfLabel.classList.toggle('items-center', isFriend);
        }

        document.querySelectorAll('#main-form-fields .per-friend-field').forEach(function (el) {
            el.style.display = isFriend ? 'none' : '';
        });
    }

    document.querySelectorAll('.reg-mode-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { setMode(btn.dataset.regMode); });
    });

    function syncHiddenField() {
        var container = document.getElementById('friend-codes-container');
        container.innerHTML = '';
        friendCodes.forEach(function (code) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'friend_codes[]';
            input.value = code;
            container.appendChild(input);
        });
    }

    function syncFriendAnswers() {
        var container = document.getElementById('friend-answers-container');
        container.innerHTML = '';
        friendCodes.forEach(function (code) {
            var entry = friendEntries[code] || {};
            var answers = entry.answers || {};
            Object.keys(answers).forEach(function (key) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'friend_answers[' + code + '][' + key + ']';
                input.value = answers[key];
                container.appendChild(input);
            });
        });
    }

    function renderFriendFields(code) {
        if (friendFields.length === 0) return '';

        var entry = friendEntries[code] || {};
        var answers = entry.answers || {};
        var html = '';

        friendFields.forEach(function (field) {
            var val = answers[field.key] || '';
            var id = 'ff_' + code + '_' + field.key;

            if (field.type === 'select') {
                html += '<div class="mt-2">';
                html += '<label for="' + id + '" class="text-xs font-semibold text-slate-600">' + field.label + '</label>';
                html += '<select id="' + id + '" data-code="' + code + '" data-key="' + field.key + '"' +
                    (field.required ? ' required' : '') +
                    ' class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">';
                html += '<option value="">-- Pilih --</option>';
                field.options.forEach(function (opt) {
                    html += '<option value="' + opt + '"' + (val === opt ? ' selected' : '') + '>' + opt + '</option>';
                });
                html += '</select></div>';
            } else if (field.type === 'radio') {
                html += '<div class="mt-2">';
                html += '<p class="text-xs font-semibold text-slate-600">' + field.label + '</p>';
                html += '<div class="mt-1 flex flex-wrap gap-3">';
                field.options.forEach(function (opt) {
                    var radioId = id + '_' + opt.replace(/\s+/g, '_');
                    html += '<label for="' + radioId + '" class="flex items-center gap-1.5 text-xs text-slate-700">';
                    html += '<input type="radio" id="' + radioId + '" name="ff_' + code + '_' + field.key + '" value="' + opt + '"' +
                        ' data-code="' + code + '" data-key="' + field.key + '"' +
                        (field.required ? ' required' : '') +
                        ' class="h-3 w-3 border-slate-300 text-emerald-600 focus:ring-emerald-500">';
                    html += opt;
                    html += '</label>';
                });
                html += '</div></div>';
            }
        });

        return html;
    }

    function renderFriendList() {
        var list = document.getElementById('friend-list');
        var hint = document.getElementById('friend-empty-hint');
        list.innerHTML = '';
        hint.classList.toggle('hidden', friendCodes.length > 0);

        friendCodes.forEach(function (code) {
            var entry = friendEntries[code] || {};
            var card = document.createElement('div');
            card.className = 'rounded-lg border border-emerald-200 bg-white p-3 text-sm';
            card.innerHTML =
                '<div class="flex items-center justify-between">' +
                    '<div class="min-w-0 flex-1">' +
                        '<p class="font-bold text-slate-900 truncate">' + (entry.name || code) + '</p>' +
                        '<p class="mt-0.5 text-xs text-slate-500 truncate">' +
                            [entry.email, entry.nim_nip, entry.institution].filter(Boolean).join(' · ') +
                        '</p>' +
                    '</div>' +
                    '<button type="button" data-remove="' + code + '"' +
                        ' class="ml-3 shrink-0 rounded-lg border border-red-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">' +
                        '✕' +
                    '</button>' +
                '</div>' +
                renderFriendFields(code);
            list.appendChild(card);
        });

        var input = document.getElementById('friend-code-input');
        var addBtn = document.getElementById('friend-search-btn');
        if (friendCodes.length >= MAX_FRIENDS) {
            input.disabled = true;
            input.placeholder = 'Maksimal ' + MAX_FRIENDS + ' teman';
            addBtn.disabled = true;
            addBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            input.disabled = false;
            input.placeholder = 'ML-XXXXXXXX';
            addBtn.disabled = false;
            addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    document.getElementById('friend-list').addEventListener('click', function (e) {
        var btn = e.target.closest('[data-remove]');
        if (!btn) return;
        var code = btn.getAttribute('data-remove');
        friendCodes = friendCodes.filter(function (c) { return c !== code; });
        delete friendEntries[code];
        syncHiddenField();
        syncFriendAnswers();
        renderFriendList();
    });

    document.getElementById('friend-list').addEventListener('change', function (e) {
        var el = e.target;
        var code = el.dataset.code;
        var key = el.dataset.key;
        if (!code || !key) return;
        if (!friendEntries[code]) friendEntries[code] = {};
        if (!friendEntries[code].answers) friendEntries[code].answers = {};
        friendEntries[code].answers[key] = el.value;
        syncFriendAnswers();
    });

    document.getElementById('friend-search-btn').addEventListener('click', function () {
        var input = document.getElementById('friend-code-input');
        var code = input.value.trim();
        var error = document.getElementById('friend-error');
        error.classList.add('hidden');

        if (!code) {
            error.textContent = 'Masukkan kode partisipan teman terlebih dahulu.';
            error.classList.remove('hidden');
            return;
        }

        if (friendCodes.indexOf(code) !== -1) {
            error.textContent = 'Kode tersebut sudah ditambahkan.';
            error.classList.remove('hidden');
            return;
        }

        if (friendCodes.length >= MAX_FRIENDS) {
            error.textContent = 'Maksimal ' + MAX_FRIENDS + ' teman per event.';
            error.classList.remove('hidden');
            return;
        }

        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Mencari...';

        fetch(searchUrl + '?kode=' + encodeURIComponent(code), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.found) {
                error.textContent = 'Kode tidak ditemukan atau tidak dapat didaftarkan.';
                error.classList.remove('hidden');
                return;
            }

            friendCodes.push(code);
            friendEntries[code] = {
                name: data.name,
                email: data.email,
                nim_nip: data.nim_nip,
                institution: data.institution,
                answers: {}
            };

            syncHiddenField();
            syncFriendAnswers();
            renderFriendList();
            input.value = '';
            input.focus();

            var fields = document.querySelectorAll('#main-form-fields input[name], #main-form-fields select[name], #main-form-fields textarea[name]');
            var mapping = {
                nim: data.nim_nip, nim_nip: data.nim_nip, nip: data.nim_nip,
                nik: data.nim_nip, nidn: data.nim_nip,
                institution: data.institution, instansi: data.institution,
                lembaga: data.institution, sekolah: data.institution,
                name: data.name, nama: data.name,
                email: data.email
            };
            fields.forEach(function (field) {
                var key = field.getAttribute('name');
                if (key && mapping[key] !== undefined && key !== 'register_for' && key !== 'friend_codes') {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        if (String(field.value) === String(mapping[key])) field.checked = true;
                    } else {
                        field.value = mapping[key];
                    }
                }
            });
        })
        .catch(function () {
            error.textContent = 'Terjadi kesalahan saat mencari kode. Coba lagi.';
            error.classList.remove('hidden');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = '+ Tambah';
        });
    });

    document.getElementById('friend-code-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('friend-search-btn').click();
        }
    });
})();
</script>
@endpush
