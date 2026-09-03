<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') — MarketLabs</title>
    <meta name="description" content="Sistem Informasi MarketLabs - platform manajemen laboratorium yang modern, cepat, dan terintegrasi.">

    @php $faviconPath = \App\Models\Setting::get('site_favicon'); @endphp
    <link rel="icon" href="{{ $faviconPath ? asset('storage/' . $faviconPath) : asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    <div class="min-h-screen lg:flex">

        {{-- Overlay (layar kecil) --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm lg:hidden"></div>

        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Konten --}}
        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Topbar --}}
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" id="sidebar-open"
                                class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600 lg:hidden"
                                aria-label="Buka menu">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <h2 class="truncate text-lg font-bold text-slate-900">@yield('page', 'Dashboard')</h2>
                    </div>
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="hidden min-w-0 text-right sm:block">
                            <span class="block truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ Auth::user()->email }}</span>
                        </span>

                        {{-- Notifikasi --}}
                        <div class="relative" id="notif-wrap">
                            <button type="button" id="notif-btn"
                                    class="relative rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600"
                                    aria-label="Notifikasi">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                @if ($adminUnreadCount > 0)
                                    <span id="notif-badge"
                                          class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white shadow">
                                        {{ $adminUnreadCount }}
                                    </span>
                                @endif
                            </button>

                            <div id="notif-dropdown"
                                 class="absolute right-0 mt-2 hidden w-80 origin-top-right rounded-2xl border border-slate-200 bg-white shadow-xl sm:w-96">
                                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                                    <p class="text-sm font-bold text-slate-900">Notifikasi</p>
                                    <span id="notif-count-chip"
                                          class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-600 {{ $adminUnreadCount > 0 ? '' : 'hidden' }}">
                                        {{ $adminUnreadCount }} belum dibaca
                                    </span>
                                </div>

                                <div id="notif-list" class="max-h-80 overflow-y-auto">
                                    @forelse ($adminNotifications as $n)
                                        @php
                                            $nUrl = $n->data['url'] ?? route('admin.dashboard');
                                            $nTitle = $n->data['title'] ?? 'Notifikasi';
                                            $nMessage = $n->data['message'] ?? null;
                                        @endphp
                                        <a href="{{ $nUrl }}"
                                           class="notif-item flex items-start gap-3 border-b border-slate-50 px-5 py-3.5 transition hover:bg-emerald-50/60">
                                            <span class="mt-1.5 h-2 w-2 flex-none rounded-full {{ $n->read_at ? 'bg-slate-200' : 'bg-emerald-500' }}"></span>
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $nTitle }}</span>
                                                @if ($nMessage)
                                                    <span class="block truncate text-xs text-slate-500">{{ $nMessage }}</span>
                                                @endif
                                            </span>
                                            <span class="ml-auto flex-none text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                                        </a>
                                    @empty
                                        <div class="px-5 py-10 text-center">
                                            <p class="text-sm font-semibold text-slate-700">Tidak ada notifikasi</p>
                                            <p class="mt-1 text-xs text-slate-500">Notifikasi untuk Anda akan muncul di sini.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-5 py-3">
                                    <form action="{{ route('notifications.read') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs font-semibold text-emerald-700 transition hover:text-emerald-900">
                                            Tandai Semua Dibaca
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.notifications.all') }}"
                                       class="text-xs font-semibold text-emerald-700 transition hover:text-emerald-900">
                                        Lihat Semua Notifikasi →
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('home') }}"
                           class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                            Lihat Beranda
                        </a>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Notifikasi (dropdown lonceng + polling realtime) --}}
    <script>
        (function () {
            const wrap = document.getElementById('notif-wrap');
            const btn = document.getElementById('notif-btn');
            const dropdown = document.getElementById('notif-dropdown');

            if (!wrap || !btn || !dropdown) return;

            const list = document.getElementById('notif-list');
            const chip = document.getElementById('notif-count-chip');
            const badge = document.getElementById('notif-badge');
            const pollUrl = '{{ route('admin.notifications') }}';
            let lastCount = parseInt(badge ? badge.textContent : '0', 10) || 0;

            function closeNotif() {
                dropdown.classList.add('hidden');
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!wrap.contains(e.target)) closeNotif();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeNotif();
            });

            function emptyMarkup() {
                return '<div class="px-5 py-10 text-center">' +
                    '<p class="text-sm font-semibold text-slate-700">Tidak ada notifikasi</p>' +
                    '<p class="mt-1 text-xs text-slate-500">Notifikasi untuk Anda akan muncul di sini.</p>' +
                    '</div>';
            }

            function esc(value) {
                return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function itemMarkup(item) {
                const dot = item.is_read ? 'bg-slate-200' : 'bg-emerald-500';
                const message = item.message ? '<span class="block truncate text-xs text-slate-500">' + esc(item.message) + '</span>' : '';
                return '<a href="' + esc(item.url) + '" class="notif-item flex items-start gap-3 border-b border-slate-50 px-5 py-3.5 transition hover:bg-emerald-50/60">' +
                    '<span class="mt-1.5 h-2 w-2 flex-none rounded-full ' + dot + '"></span>' +
                    '<span class="min-w-0">' +
                    '<span class="block truncate text-sm font-semibold text-slate-900">' + esc(item.title) + '</span>' +
                    message +
                    '</span>' +
                    '<span class="ml-auto flex-none text-xs text-slate-400">' + esc(item.time) + '</span>' +
                    '</a>';
            }

            function render(data) {
                const count = parseInt(data.unread_count, 10) || 0;
                let badgeEl = document.getElementById('notif-badge');

                // Badge lonceng
                if (count > 0) {
                    if (badgeEl) {
                        badgeEl.textContent = count;
                        badgeEl.classList.remove('hidden');
                    } else {
                        btn.insertAdjacentHTML('beforeend',
                            '<span id="notif-badge" class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white shadow">' + count + '</span>');
                        badgeEl = document.getElementById('notif-badge');
                    }
                    if (count > lastCount && badgeEl) {
                        badgeEl.classList.remove('animate-ping');
                        void badgeEl.offsetWidth;
                        badgeEl.classList.add('animate-ping');
                        setTimeout(function () { badgeEl.classList.remove('animate-ping'); }, 1000);
                    }
                } else if (badgeEl) {
                    badgeEl.classList.add('hidden');
                }

                // Chip header
                if (chip) {
                    chip.textContent = count + ' belum dibaca';
                    chip.classList.toggle('hidden', count === 0);
                }

                // Daftar item
                if (list) {
                    list.innerHTML = count > 0 ? data.items.map(itemMarkup).join('') : emptyMarkup();
                }

                lastCount = count;
            }

            // Polling realtime: perbarui notifikasi tanpa refresh halaman.
            async function refresh() {
                try {
                    const res = await fetch(pollUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    render(data);
                } catch (e) {
                    // Abaikan error jaringan; coba lagi pada interval berikutnya.
                }
            }

            refresh();
            setInterval(refresh, 3000);
        })();
    </script>

    {{-- Toggle sidebar untuk layar kecil --}}
    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openBtn = document.getElementById('sidebar-open');
            const closeBtn = document.getElementById('sidebar-close');

            if (!sidebar || !overlay || !openBtn || !closeBtn) return;

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openBtn.addEventListener('click', openSidebar);
            closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSidebar();
            });

            // Tutup otomatis setelah memilih menu di layar kecil
            sidebar.querySelectorAll('nav a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 1024) closeSidebar();
                });
            });

            // Pastikan tertutup saat resize ke desktop, terbuka di desktop
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        })();
    </script>

    @include('partials.confirm-modal')

    {{-- Modal Konfirmasi Bulk Delete --}}
    <div id="bulk-confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeBulkModal()"></div>
        <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h3 class="mt-4 text-center text-lg font-bold text-slate-900">Hapus Terpilih?</h3>
            <p id="bulk-confirm-message" class="mt-2 text-center text-sm leading-relaxed text-slate-600"></p>
            <div class="mt-6 flex justify-center gap-3">
                <button type="button" onclick="closeBulkModal()"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                    Batal
                </button>
                <button type="button" id="bulk-confirm-accept"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Ya, Hapus Semua
                </button>
            </div>
        </div>
    </div>
    <script>
    function openBulkModal(count, callback) {
        var modal = document.getElementById('bulk-confirm-modal');
        var msg = document.getElementById('bulk-confirm-message');
        var accept = document.getElementById('bulk-confirm-accept');
        msg.textContent = count + ' item akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        var handler = function () {
            accept.removeEventListener('click', handler);
            closeBulkModal();
            callback();
        };
        accept.addEventListener('click', handler);
    }
    function closeBulkModal() {
        var modal = document.getElementById('bulk-confirm-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }
    </script>

    @stack('scripts')

</body>
</html>
