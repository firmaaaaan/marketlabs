<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Staff') — MarketLabs</title>
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
        <aside id="sidebar"
               class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-gradient-to-b from-emerald-700 to-emerald-900 shadow-2xl transition-transform duration-300 ease-in-out lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:translate-x-0 lg:shadow-none">
            {{-- Header sidebar --}}
            <div class="flex items-center justify-between px-6 py-5">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    @php $siteLogo = \App\Models\Setting::get('site_logo'); @endphp
                    @if ($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo MarketLabs" class="h-11 w-11 object-contain">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-sm font-bold text-emerald-700 shadow-md">M</span>
                    @endif
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white">
                            Market<span class="text-emerald-300">Labs</span>
                        </span>
                        <p class="text-[10px] font-semibold leading-tight text-emerald-300/80">by UPT Laboratorium Terpadu</p>
                    </div>
                </a>
                <button type="button" id="sidebar-close"
                        class="rounded-lg p-2 text-emerald-100 transition hover:bg-white/10 hover:text-white lg:hidden"
                        aria-label="Tutup menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="mt-4 flex-1 space-y-1 overflow-y-auto px-3">
                <p class="mt-2 mb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-emerald-300/70">Menu</p>

                <a href="{{ route('notifications.all') }}"
                   class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-emerald-100 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('notifications.all') ? 'bg-white/10 font-semibold text-white' : '' }}">
                    <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    Notifikasi
                    @if ($staffUnreadCount > 0)
                        <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">
                            {{ $staffUnreadCount }}
                        </span>
                    @endif
                </a>

                @if (Auth::user()->isLaboran())
                    <a href="{{ route('laboran.index') }}"
                       class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-emerald-100 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('laboran.*') ? 'bg-white/10 font-semibold text-white' : '' }}">
                        <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Halaman Laboran
                    </a>
                @endif

                <p class="mt-6 mb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-emerald-300/70">Akun</p>
                <a href="{{ route('profile.show') }}"
                   class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-emerald-100 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Profil
                </a>
                <a href="{{ route('home') }}"
                   class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-emerald-100 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                    </svg>
                    Beranda
                </a>
            </nav>

            <div class="border-t border-white/10 px-4 py-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-emerald-100 transition hover:bg-white/10 hover:text-white">
                        <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

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
                        <h2 class="truncate text-lg font-bold text-slate-900">@yield('page', 'Staff')</h2>
                    </div>
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="hidden min-w-0 text-right sm:block">
                            <span class="block truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ Auth::user()->role_label }}</span>
                        </span>

                        {{-- Notifikasi --}}
                        <div class="relative" id="staff-notif-wrap">
                            <button type="button" id="staff-notif-btn"
                                    class="relative rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600"
                                    aria-label="Notifikasi">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                @if ($staffUnreadCount > 0)
                                    <span id="staff-notif-badge"
                                          class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white shadow">
                                        {{ $staffUnreadCount }}
                                    </span>
                                @endif
                            </button>

                            <div id="staff-notif-dropdown"
                                 class="absolute right-0 mt-2 hidden w-80 origin-top-right rounded-2xl border border-slate-200 bg-white shadow-xl sm:w-96">
                                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                                    <p class="text-sm font-bold text-slate-900">Notifikasi</p>
                                    <span id="staff-notif-chip"
                                          class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-600 {{ $staffUnreadCount > 0 ? '' : 'hidden' }}">
                                        {{ $staffUnreadCount }} belum dibaca
                                    </span>
                                </div>

                                <div id="staff-notif-list" class="max-h-80 overflow-y-auto">
                                    @forelse ($staffNotifications as $n)
                                        @php
                                            $nUrl = $n->data['url'] ?? route('laboran.index');
                                            $nTitle = $n->data['title'] ?? 'Notifikasi';
                                            $nMessage = $n->data['message'] ?? null;
                                        @endphp
                                        <a href="{{ $nUrl }}"
                                           class="staff-notif-item flex items-start gap-3 border-b border-slate-50 px-5 py-3.5 transition hover:bg-emerald-50/60">
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
                                    <a href="{{ route('notifications.all') }}"
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
            const wrap = document.getElementById('staff-notif-wrap');
            const btn = document.getElementById('staff-notif-btn');
            const dropdown = document.getElementById('staff-notif-dropdown');

            if (!wrap || !btn || !dropdown) return;

            const list = document.getElementById('staff-notif-list');
            const chip = document.getElementById('staff-notif-chip');
            const badge = document.getElementById('staff-notif-badge');
            const pollUrl = '{{ route('notifications.index') }}';
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

            function esc(value) {
                return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function itemMarkup(item) {
                const dot = item.is_read ? 'bg-slate-200' : 'bg-emerald-500';
                const message = item.message ? '<span class="block truncate text-xs text-slate-500">' + esc(item.message) + '</span>' : '';
                return '<a href="' + esc(item.url) + '" class="staff-notif-item flex items-start gap-3 border-b border-slate-50 px-5 py-3.5 transition hover:bg-emerald-50/60">' +
                    '<span class="mt-1.5 h-2 w-2 flex-none rounded-full ' + dot + '"></span>' +
                    '<span class="min-w-0">' +
                    '<span class="block truncate text-sm font-semibold text-slate-900">' + esc(item.title) + '</span>' +
                    message +
                    '</span>' +
                    '<span class="ml-auto flex-none text-xs text-slate-400">' + esc(item.time) + '</span>' +
                    '</a>';
            }

            function emptyMarkup() {
                return '<div class="px-5 py-10 text-center">' +
                    '<p class="text-sm font-semibold text-slate-700">Tidak ada notifikasi</p>' +
                    '<p class="mt-1 text-xs text-slate-500">Notifikasi untuk Anda akan muncul di sini.</p>' +
                    '</div>';
            }

            function render(data) {
                const count = parseInt(data.unread_count, 10) || 0;
                let badgeEl = document.getElementById('staff-notif-badge');

                if (count > 0) {
                    if (badgeEl) {
                        badgeEl.textContent = count;
                        badgeEl.classList.remove('hidden');
                    } else {
                        btn.insertAdjacentHTML('beforeend',
                            '<span id="staff-notif-badge" class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white shadow">' + count + '</span>');
                        badgeEl = document.getElementById('staff-notif-badge');
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

                if (chip) {
                    chip.textContent = count + ' belum dibaca';
                    chip.classList.toggle('hidden', count === 0);
                }

                if (list) {
                    list.innerHTML = count > 0 ? data.items.map(itemMarkup).join('') : emptyMarkup();
                }

                lastCount = count;
            }

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

            sidebar.querySelectorAll('nav a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 1024) closeSidebar();
                });
            });

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

    @stack('scripts')

</body>
</html>
