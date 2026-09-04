<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistem Informasi MarketLabs')</title>
    <meta name="description" content="MarketLabs - Beranda.">

    @php $faviconPath = \App\Models\Setting::get('site_favicon'); @endphp
    <link rel="icon" href="{{ $faviconPath ? asset('storage/' . $faviconPath) : asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-slate-800 antialiased">

    {{-- Navbar --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-slate-200 bg-white shadow-sm">
        {{-- Topbar informasi --}}
        @php
            $topbarPhone = \App\Models\Setting::get('footer_phone', '+6281234567890');
            $topbarPhoneLink = preg_replace('/\D/', '', $topbarPhone);
            if (str_starts_with($topbarPhoneLink, '0')) {
                $topbarPhoneLink = '62'.substr($topbarPhoneLink, 1);
            }
            $siteLogo = \App\Models\Setting::get('site_logo');
        @endphp
        <div class="bg-gradient-to-r from-emerald-700 via-emerald-800 to-emerald-800 text-white">
            <div class="mx-auto flex h-9 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <p class="truncate text-[11px] font-semibold tracking-wide sm:text-xs">
                    UPT LABORATORIUM TERPADU - UNIVERSITAS 'AISYIYAH YOGYAKARTA
                </p>
                <a href="tel:{{ $topbarPhoneLink }}" class="flex flex-none items-center gap-1.5 text-[11px] font-bold transition hover:text-emerald-200 sm:text-xs">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ $topbarPhone }}
                </a>
            </div>
        </div>
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2">
                {{-- Tombol menu mobile --}}
                <button type="button" id="mobile-menu-btn"
                        class="rounded-xl p-2 text-slate-600 transition-all hover:bg-emerald-50 hover:text-emerald-600 md:hidden"
                        aria-label="Buka menu" aria-expanded="false" aria-controls="mobile-menu">
                    <svg id="mobile-menu-icon-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg id="mobile-menu-icon-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <a href="#beranda" class="flex items-center gap-2.5">
                    @if ($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo MarketLabs" class="h-12 w-12 object-contain transition-transform hover:scale-105">
                    @else
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-600 text-sm font-bold text-white shadow-lg shadow-emerald-600/30 transition-transform hover:scale-105">M</span>
                    @endif
                    <div>
                        <span class="text-lg font-bold tracking-tight text-slate-900">
                            Market<span class="text-emerald-600">Labs</span>
                        </span>
                        <p class="text-[10px] font-semibold leading-tight text-slate-500">by UPT Laboratorium Terpadu</p>
                    </div>
                </a>
            </div>

            <div class="hidden items-center gap-6 md:flex">
                <a href="{{ route('home') }}" class="px-1 py-1 text-sm font-medium text-slate-600 transition-all hover:text-emerald-600">Beranda</a>
                <a href="{{ route('tools.index') }}" class="px-1 py-1 text-sm font-medium text-slate-600 transition-all hover:text-emerald-600">Alat</a>
                <a href="{{ route('sample-tests.catalog') }}" class="px-1 py-1 text-sm font-medium text-slate-600 transition-all hover:text-emerald-600">Pengujian</a>
                <a href="{{ route('lab-schedule') }}" class="px-1 py-1 text-sm font-medium text-slate-600 transition-all hover:text-emerald-600">Jadwal Lab</a>
                <a href="{{ route('events.index') }}" class="px-1 py-1 text-sm font-medium text-slate-600 transition-all hover:text-emerald-600">Event</a>
                <a href="{{ route('health-checkups.catalog') }}" class="px-1 py-1 text-sm font-medium text-slate-600 transition-all hover:text-emerald-600">Kesehatan</a>
                <a href="{{ route('research.create') }}" class="px-1 py-1 text-sm font-medium text-slate-600 transition-all hover:text-emerald-600">Riset &amp; Penelitian</a>
            </div>

            @php
                $cartCount = array_sum(session('cart', [])) + count(session('test_cart', []));
            @endphp

            <div class="flex items-center gap-3">
                <a href="{{ route('cart.index') }}" class="relative rounded-xl p-2.5 text-slate-600 transition-all hover:bg-emerald-50 hover:text-emerald-600" aria-label="Keranjang peminjaman">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                    @if ($cartCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 flex h-5 w-5 items-center justify-center rounded-full badge-premium text-[10px] font-bold text-white">
                            {{ min($cartCount, 99) }}
                        </span>
                    @endif
                </a>

                @auth
                    <div class="flex items-center gap-3">
                        {{-- Notifikasi peminjaman --}}
                        <div class="relative" id="client-notif-wrap">
                            <button type="button" id="client-notif-btn"
                                    class="relative p-2 text-slate-600 transition hover:text-emerald-600"
                                    aria-label="Notifikasi">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                @if ($clientUnreadCount > 0)
                                    <span id="client-notif-badge"
                                          class="absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white shadow">
                                        {{ min($clientUnreadCount, 99) }}
                                    </span>
                                @endif
                            </button>

                            <div id="client-notif-dropdown"
                                 class="absolute right-0 mt-2 hidden w-80 origin-top-right rounded-2xl border border-slate-200 bg-white shadow-xl sm:w-96">
                                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                                    <p class="text-sm font-bold text-slate-900">Notifikasi</p>
                                    <span id="client-notif-chip"
                                          class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 {{ $clientUnreadCount > 0 ? '' : 'hidden' }}">
                                        {{ $clientUnreadCount }} baru
                                    </span>
                                </div>

                                <div id="client-notif-list" class="max-h-80 overflow-y-auto">
                                    @forelse ($clientNotifications as $n)
                                        <a href="{{ $n->data['url'] ?? route('borrowings.index') }}"
                                           class="notif-item flex items-start gap-3 border-b border-slate-50 px-5 py-3.5 transition hover:bg-emerald-50/60 {{ $n->read_at ? 'opacity-60' : '' }}">
                                            <span class="mt-1 h-2 w-2 flex-none rounded-full {{ $n->read_at ? 'bg-slate-200' : 'bg-emerald-500' }}"></span>
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                                                @if (! empty($n->data['message']))
                                                    <span class="block truncate text-xs text-slate-500">{{ $n->data['message'] }}</span>
                                                @endif
                                            </span>
                                            <span class="ml-auto flex-none text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                                        </a>
                                    @empty
                                        <div class="px-5 py-10 text-center">
                                            <p class="text-sm font-semibold text-slate-700">Belum ada notifikasi</p>
                                            <p class="mt-1 text-xs text-slate-500">Perubahan status peminjaman Anda akan muncul di sini.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <a href="{{ route('notifications.all') }}"
                                   class="block rounded-b-2xl border-t border-slate-100 bg-slate-50 px-5 py-3 text-center text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50/50">
                                    Lihat Semua Notifikasi
                                </a>
                            </div>
                        </div>

                        <div class="relative" id="user-menu-wrap">
                            <button type="button" id="user-menu-btn"
                                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 transition-all hover:border-emerald-300 hover:shadow-md sm:px-3.5"
                                    aria-label="Menu akun">
                                <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-gradient-to-br from-emerald-600 to-emerald-600 text-xs font-bold text-white shadow-md shadow-emerald-600/20">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden max-w-32 truncate text-sm font-semibold text-slate-700 sm:block">{{ Auth::user()->nim_nip }} - {{ Auth::user()->name }}</span>
                                <svg class="hidden h-4 w-4 flex-none text-slate-400 sm:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div id="user-menu-dropdown"
                                 class="absolute right-0 mt-2 hidden w-56 origin-top-right rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl">
                                <div class="border-b border-slate-100 px-4 py-2.5">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->nim_nip }} - {{ Auth::user()->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
                                </div>

                                <a href="{{ route('profile.show') }}"
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">
                                    <svg class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Profil
                                </a>

                                @if (Auth::user()->isLaboran())
                                    <a href="{{ route('laboran.index') }}"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">
                                        <svg class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                        Halaman Laboran
                                    </a>
                                @endif
                                @if (Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">
                                        <svg class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                        </svg>
                                        Admin
                                    </a>
                                @endif

                                <form action="{{ route('logout') }}" method="POST" class="border-t border-slate-100 pt-1">
                                    @csrf
                                    <button type="submit"
                                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50">
                                        <svg class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}"
                           class="hidden text-sm font-semibold text-slate-600 transition-all hover:text-emerald-600 sm:block">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}"
                           class="btn-premium rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/25 hover:from-emerald-700 hover:to-emerald-700">
                            Daftar
                        </a>
                    </div>
                @endauth
            </div>
        </nav>

        {{-- Menu mobile --}}
        <div id="mobile-menu" class="hidden border-t border-slate-200 bg-white md:hidden">
            <nav class="max-h-[calc(100vh-4rem)] space-y-1 overflow-y-auto px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">Beranda</a>
                <a href="{{ route('tools.index') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">Alat</a>
                <a href="{{ route('sample-tests.catalog') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">Pengujian</a>
                <a href="{{ route('lab-schedule') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">Jadwal Lab</a>
                <a href="{{ route('events.index') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">Event</a>
                <a href="{{ route('health-checkups.catalog') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">Kesehatan</a>
                <a href="{{ route('research.create') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">Riset &amp; Penelitian</a>
                @auth
                    @if (Auth::user()->isLaboran())
                        <a href="{{ route('laboran.index') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">Halaman Laboran</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    {{-- Konten --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Menu mobile (hamburger) --}}
    <script>
        (function () {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('mobile-menu-icon-open');
            const iconClose = document.getElementById('mobile-menu-icon-close');

            if (!btn || !menu) return;

            function isOpen() {
                return !menu.classList.contains('hidden');
            }

            function openMenu() {
                menu.classList.remove('hidden');
                iconOpen.classList.add('hidden');
                iconClose.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
            }

            function closeMenu() {
                menu.classList.add('hidden');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                isOpen() ? closeMenu() : openMenu();
            });

            // Tutup saat memilih salah satu link di menu.
            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });

            // Tutup saat klik di luar atau tombol Escape.
            document.addEventListener('click', function (e) {
                if (!btn.contains(e.target) && !menu.contains(e.target)) closeMenu();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMenu();
            });

            // Pastikan tertutup saat pindah ke desktop.
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 768) closeMenu();
            });
        })();
    </script>

    {{-- Notifikasi client (lonceng + polling realtime) --}}
    @auth
    <script>
        (function () {
            const wrap = document.getElementById('client-notif-wrap');
            const btn = document.getElementById('client-notif-btn');
            const dropdown = document.getElementById('client-notif-dropdown');

            if (!wrap || !btn || !dropdown) return;

            const list = document.getElementById('client-notif-list');
            const chip = document.getElementById('client-notif-chip');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let opened = false;

            function closeNotif() {
                dropdown.classList.add('hidden');
            }

            function emptyMarkup() {
                return '<div class="px-5 py-10 text-center">' +
                    '<p class="text-sm font-semibold text-slate-700">Belum ada notifikasi</p>' +
                    '<p class="mt-1 text-xs text-slate-500">Perubahan status peminjaman Anda akan muncul di sini.</p>' +
                    '</div>';
            }

            function itemMarkup(item) {
                const url = item.url || '{{ route('borrowings.index') }}';
                const dot = item.is_read ? 'bg-slate-200' : 'bg-emerald-500';
                const dim = item.is_read ? 'opacity-60' : '';
                return '<a href="' + url + '" class="notif-item flex items-start gap-3 border-b border-slate-50 px-5 py-3.5 transition hover:bg-emerald-50/60 ' + dim + '">' +
                    '<span class="mt-1 h-2 w-2 flex-none rounded-full ' + dot + '"></span>' +
                    '<span class="min-w-0">' +
                    '<span class="block truncate text-sm font-semibold text-slate-900">' + item.title + '</span>' +
                    (item.message ? '<span class="block truncate text-xs text-slate-500">' + item.message + '</span>' : '') +
                    '</span>' +
                    '<span class="ml-auto flex-none text-xs text-slate-400">' + item.time + '</span>' +
                    '</a>';
            }

            function render(data) {
                const count = parseInt(data.unread_count, 10) || 0;
                let badge = document.getElementById('client-notif-badge');

                if (count > 0) {
                    if (badge) {
                        badge.textContent = Math.min(count, 99);
                        badge.classList.remove('hidden');
                    } else {
                        btn.insertAdjacentHTML('beforeend',
                            '<span id="client-notif-badge" class="absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white shadow">' + Math.min(count, 99) + '</span>');
                    }
                } else if (badge) {
                    badge.classList.add('hidden');
                }

                if (chip) {
                    chip.textContent = count + ' baru';
                    chip.classList.toggle('hidden', count === 0);
                }

                if (list) {
                    list.innerHTML = data.items.length > 0 ? data.items.map(itemMarkup).join('') : emptyMarkup();
                }
            }

            // Polling realtime tanpa refresh halaman.
            async function refresh() {
                try {
                    const res = await fetch('{{ route('notifications.index') }}', { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    render(await res.json());
                } catch (e) {
                    // Abaikan error jaringan.
                }
            }

            // Tandai semua dibaca saat dropdown dibuka.
            async function markAllRead() {
                try {
                    await fetch('{{ route('notifications.read') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                    });
                } catch (e) { /* abaikan */ }
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const willOpen = dropdown.classList.contains('hidden');
                dropdown.classList.toggle('hidden');

                if (willOpen) {
                    opened = true;
                    markAllRead().then(refresh);
                }
            });

            document.addEventListener('click', function (e) {
                if (!wrap.contains(e.target)) closeNotif();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeNotif();
            });

            refresh();
            setInterval(refresh, 3000);
        })();
    </script>

    {{-- Menu akun (dropdown nama user) --}}
    <script>
        (function () {
            const wrap = document.getElementById('user-menu-wrap');
            const btn = document.getElementById('user-menu-btn');
            const dropdown = document.getElementById('user-menu-dropdown');

            if (!wrap || !btn || !dropdown) return;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!wrap.contains(e.target)) dropdown.classList.add('hidden');
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') dropdown.classList.add('hidden');
            });
        })();
    </script>

    @endauth

    {{-- Tombol WhatsApp mengambang --}}
    @php
        $waEnabled = \App\Models\Setting::get('whatsapp_enabled') === '1';
        $waNumber = \App\Models\Setting::get('whatsapp_number', '');
        $waMessage = \App\Models\Setting::get('whatsapp_message', 'Halo Admin MarketLabs, saya ingin bertanya tentang layanan laboratorium.');
    @endphp
    @if ($waEnabled && $waNumber)
        <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode($waMessage) }}"
           target="_blank" rel="noopener noreferrer"
           class="fixed right-5 bottom-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl shadow-emerald-900/30 transition duration-300 hover:scale-110 hover:bg-[#20bd5a]"
           aria-label="Chat WhatsApp">
            <svg class="h-7 w-7" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                <path d="M16.004 3.2c-7.06 0-12.8 5.74-12.8 12.8 0 2.257.59 4.461 1.712 6.4L3.2 28.8l6.56-1.682a12.74 12.74 0 006.244 1.594h.005c7.06 0 12.8-5.74 12.8-12.8 0-3.42-1.332-6.634-3.75-9.05a12.72 12.72 0 00-9.055-3.662zm0 23.36h-.004a10.6 10.6 0 01-5.4-1.48l-.387-.23-3.89.997 1.04-3.79-.253-.39a10.55 10.55 0 01-1.626-5.667c0-5.853 4.764-10.617 10.624-10.617a10.54 10.54 0 017.49 3.105 10.54 10.54 0 013.134 7.507c0 5.854-4.765 10.585-10.624 10.585zm5.827-7.95c-.32-.16-1.89-.933-2.183-1.04-.293-.107-.506-.16-.72.16-.213.32-.826 1.04-1.013 1.253-.187.214-.373.24-.693.08-.32-.16-1.35-.497-2.57-1.585-.95-.847-1.592-1.893-1.778-2.213-.187-.32-.02-.493.14-.653.144-.144.32-.373.48-.56.16-.187.213-.32.32-.533.107-.214.053-.4-.027-.56-.08-.16-.72-1.734-.987-2.374-.26-.625-.524-.54-.72-.55h-.613c-.213 0-.56.08-.853.4-.293.32-1.12 1.094-1.12 2.667s1.147 3.093 1.307 3.307c.16.213 2.257 3.446 5.467 4.833.764.33 1.36.527 1.825.674.767.244 1.465.21 2.017.127.615-.092 1.893-.773 2.16-1.52.267-.747.267-1.387.187-1.52-.08-.134-.293-.214-.613-.374z" />
            </svg>
        </a>
    @endif

    {{-- Footer --}}
    <footer class="border-t border-slate-200/80 bg-slate-50/80/50">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-12 md:grid-cols-3">
                {{-- Brand & alamat --}}
                <div>
                    <div class="flex items-center gap-2.5">
                        @if ($siteLogo)
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo MarketLabs" class="h-12 w-12 object-contain">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-600 text-sm font-bold text-white shadow-lg shadow-emerald-600/30">M</span>
                        @endif
                        <div>
                            <span class="text-lg font-bold text-slate-900">
                                Market<span class="text-emerald-600">Labs</span>
                            </span>
                            <p class="text-[10px] font-semibold leading-tight text-slate-500">by UPT Laboratorium Terpadu</p>
                        </div>
                    </div>
                    @php
                        $footerLogos = App\Models\FooterLogo::forFooter();
                    @endphp
                    @if ($footerLogos->isNotEmpty())
                        <div class="mt-4 flex flex-wrap items-center gap-4">
                            @foreach ($footerLogos as $footerLogo)
                                @if ($footerLogo->url)
                                    <a href="{{ $footerLogo->url }}" target="_blank" rel="noopener" title="{{ $footerLogo->name }}">
                                        <img src="{{ $footerLogo->image_url }}" alt="{{ $footerLogo->name }}"
                                             class="h-9 w-auto object-contain transition hover:opacity-75">
                                    </a>
                                @else
                                    <img src="{{ $footerLogo->image_url }}" alt="{{ $footerLogo->name }}"
                                         class="h-9 w-auto object-contain">
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <p class="mt-4 flex items-start gap-2.5 text-sm leading-relaxed text-slate-500">
                        <svg class="mt-0.5 h-5 w-5 flex-none text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{!! nl2br(e(\App\Models\Setting::get('footer_address', 'Jl. Laboratorium Teknologi No. 123, Bandung, Jawa Barat'))) !!}</span>
                    </p>
                </div>

                {{-- Kontak --}}
                <div>
                    <p class="text-sm font-bold text-slate-900">Kontak</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-500">
                        <li class="flex items-center gap-2.5">
                            <svg class="h-5 w-5 flex-none text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            @php
                                $footerPhone = \App\Models\Setting::get('footer_phone', '+6281234567890');
                                $footerPhoneLink = preg_replace('/\D/', '', $footerPhone);
                                if (str_starts_with($footerPhoneLink, '0')) {
                                    $footerPhoneLink = '62'.substr($footerPhoneLink, 1);
                                }
                                $footerEmail = \App\Models\Setting::get('footer_email', 'info@marketlabs.id');
                            @endphp
                            <a href="tel:{{ $footerPhoneLink }}" class="transition hover:text-emerald-600">{{ $footerPhone }}</a>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="h-5 w-5 flex-none text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="mailto:{{ $footerEmail }}" class="transition hover:text-emerald-600">{{ $footerEmail }}</a>
                        </li>
                    </ul>
                </div>

                {{-- Tautan --}}
                <div>
                    <p class="text-sm font-bold text-slate-900">Tautan</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-500">
                        <li><a href="#tentang" class="transition-all hover:text-emerald-600 hover:pl-1">Tentang</a></li>
                        <li><a href="#fitur" class="transition-all hover:text-emerald-600 hover:pl-1">Fitur</a></li>
                        <li><a href="#pemeriksaan" class="transition-all hover:text-emerald-600 hover:pl-1">Pemeriksaan</a></li>
                        <li><a href="#faq" class="transition-all hover:text-emerald-600 hover:pl-1">FAQ</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 border-t border-slate-200 pt-8 text-center text-sm text-slate-500">
                &copy; {{ date('Y') }} Sistem Informasi MarketLabs. Semua hak dilindungi.
            </div>
        </div>
    </footer>

    @include('partials.confirm-modal')
    @include('partials.cart-drawer')

    {{-- Modal Lengkapi Profil --}}
    @auth
        @if (! Auth::user()->isAdmin() && ! Auth::user()->isLaboran() && ! Auth::user()->isProfileComplete())
            <div id="profile-complete-modal"
                 class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm"
                 style="display: {{ session('success') && str_contains(session('success'), 'profil') ? 'none' : 'flex' }};">
                <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
                    <div class="text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100">
                            <svg class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                        <h2 class="mt-4 text-xl font-extrabold tracking-tight text-slate-900">Lengkapi Profil Anda</h2>
                        <p class="mt-2 text-sm text-slate-600">Silakan lengkapi informasi akun berikut sebelum dapat mengakses fitur MarketLabs.</p>
                    </div>

                    <form id="profile-complete-form" action="{{ route('profile.complete.update') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="pc_nim_nip" class="block text-sm font-semibold text-slate-700">NIM / NIP / NIDN / NIK <span class="text-red-500">*</span></label>
                            <input type="text" id="pc_nim_nip" name="nim_nip" value="{{ old('nim_nip', Auth::user()->nim_nip) }}" required
                                   placeholder="Contoh: 2101234567"
                                   class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            @error('nim_nip')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pc_institution" class="block text-sm font-semibold text-slate-700">Instansi / Universitas <span class="text-red-500">*</span></label>
                            <input type="text" id="pc_institution" name="institution" value="{{ old('institution', Auth::user()->institution) }}" required
                                   placeholder="Contoh: Universitas Ahmad Dahlan"
                                   class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            @error('institution')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pc_phone" class="block text-sm font-semibold text-slate-700">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                            <input type="tel" id="pc_phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}" required
                                   placeholder="Contoh: 081234567890"
                                   class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                            Simpan &amp; Lanjutkan
                        </button>
                    </form>

                    <p class="mt-3 text-center text-xs text-slate-400">
                        Anda dapat mengubah informasi ini kapan saja melalui halaman profil.
                    </p>
                </div>
            </div>

            <script>
                (function () {
                    const modal = document.getElementById('profile-complete-modal');
                    if (!modal) return;

                    // Cegah scroll di background
                    document.body.style.overflow = 'hidden';

                    // Cegah klik link di luar modal
                    modal.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });

                    // Cegah Escape key
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && modal.style.display !== 'none') {
                            e.stopPropagation();
                        }
                    });
                })();
            </script>
        @endif
    @endauth

    {{-- Cart Drawer --}}
    <script>
        (function () {
            var drawer = document.getElementById('cart-drawer');
            var backdrop = document.getElementById('cart-drawer-backdrop');
            var closeBtn = document.getElementById('cart-drawer-close');
            var badge = document.getElementById('cart-drawer-badge');
            var emptyEl = document.getElementById('cart-drawer-empty');
            var itemsEl = document.getElementById('cart-drawer-items');
            var toolsSection = document.getElementById('cart-drawer-tools-section');
            var testsSection = document.getElementById('cart-drawer-tests-section');
            var toolsContainer = document.getElementById('cart-drawer-tools');
            var testsContainer = document.getElementById('cart-drawer-tests');
            var footer = document.getElementById('cart-drawer-footer');
            var totalEl = document.getElementById('cart-drawer-total');

            if (!drawer || !backdrop) return;

            function openDrawer() {
                drawer.classList.remove('translate-x-full');
                drawer.classList.add('translate-x-0');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                document.body.style.overflow = 'hidden';
            }

            function closeDrawer() {
                drawer.classList.remove('translate-x-0');
                drawer.classList.add('translate-x-full');
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                document.body.style.overflow = '';
            }

            function formatRupiah(num) {
                return 'Rp ' + num.toLocaleString('id-ID');
            }

            function renderDrawer(data) {
                var toolItems = data.items || [];
                var testItems = data.testItems || [];
                var totalCount = (data.totalItems || 0) + testItems.length;
                var grandTotal = data.grandTotal || 0;

                if (totalCount > 0) {
                    badge.textContent = totalCount;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }

                if (totalCount === 0) {
                    emptyEl.classList.remove('hidden');
                    itemsEl.classList.add('hidden');
                    footer.classList.add('hidden');
                    return;
                }

                emptyEl.classList.add('hidden');
                itemsEl.classList.remove('hidden');
                footer.classList.remove('hidden');
                totalEl.textContent = formatRupiah(grandTotal);

                if (toolItems.length > 0) {
                    toolsSection.classList.remove('hidden');
                    toolsContainer.innerHTML = toolItems.map(function (item) {
                        var tool = item.tool;
                        var img = tool.image
                            ? '<img src="/storage/' + tool.image + '" alt="' + tool.name + '" class="h-full w-full object-cover">'
                            : '<svg class="h-8 w-8 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" /></svg>';
                        return '<div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">' +
                            '<div class="flex h-12 w-12 flex-none items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100">' + img + '</div>' +
                            '<div class="min-w-0 flex-1">' +
                            '<p class="truncate text-sm font-semibold text-slate-900">' + tool.name + '</p>' +
                            '<p class="text-xs text-slate-500">Qty: ' + item.quantity + ' &middot; ' + tool.formatted_price + '/hari</p>' +
                            '</div>' +
                            '<p class="text-sm font-bold text-emerald-700">' + formatRupiah(item.subtotal) + '</p>' +
                            '</div>';
                    }).join('');
                } else {
                    toolsSection.classList.add('hidden');
                }

                if (testItems.length > 0) {
                    testsSection.classList.remove('hidden');
                    testsContainer.innerHTML = testItems.map(function (item) {
                        var param = item.parameter;
                        var img = param.image
                            ? '<img src="/storage/' + param.image + '" alt="' + param.name + '" class="h-full w-full object-cover">'
                            : '<svg class="h-8 w-8 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>';
                        return '<div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">' +
                            '<div class="flex h-12 w-12 flex-none items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100">' + img + '</div>' +
                            '<div class="min-w-0 flex-1">' +
                            '<p class="truncate text-sm font-semibold text-slate-900">' + param.name + '</p>' +
                            '<p class="text-xs text-slate-500">' + (param.unit ? param.unit.name : '') + '</p>' +
                            '</div>' +
                            '<p class="text-sm font-bold text-emerald-700">' + formatRupiah(item.subtotal) + '</p>' +
                            '</div>';
                    }).join('');
                } else {
                    testsSection.classList.add('hidden');
                }
            }

            async function fetchCart() {
                try {
                    var res = await fetch('{{ route("cart.json") }}', { headers: { 'Accept': 'application/json' } });
                    if (res.ok) return await res.json();
                } catch (e) {}
                return null;
            }

            async function fetchTestCart() {
                try {
                    var res = await fetch('{{ route("test-cart.json") }}', { headers: { 'Accept': 'application/json' } });
                    if (res.ok) return await res.json();
                } catch (e) {}
                return null;
            }

            async function fetchAndRenderAll() {
                var results = await Promise.all([fetchCart(), fetchTestCart()]);
                var cartData = results[0];
                var testData = results[1];
                renderDrawer({
                    items: (cartData && cartData.items) ? cartData.items : [],
                    testItems: (testData && testData.items) ? testData.items : [],
                    totalItems: (cartData && cartData.totalItems) ? cartData.totalItems : 0,
                    grandTotal: ((cartData && cartData.totalCost) ? cartData.totalCost : 0) + ((testData && testData.total_cost) ? testData.total_cost : 0),
                });
            }

            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (backdrop) backdrop.addEventListener('click', closeDrawer);
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });

            window.CartDrawer = {
                open: function () { fetchAndRenderAll().then(openDrawer); },
                close: closeDrawer,
                refresh: fetchAndRenderAll,
            };

            fetchAndRenderAll();
        })();
    </script>

    {{-- Scroll Reveal Animation --}}
    <script>
        (function () {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe all reveal elements
            document.querySelectorAll('.reveal').forEach(el => {
                observer.observe(el);
            });

            // Also observe elements with reveal-delay classes
            document.querySelectorAll('[class*="reveal-delay"]').forEach(el => {
                observer.observe(el);
            });
        })();
    </script>

    {{-- Smooth scroll for anchor links --}}
    <script>
        (function () {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;

                    const target = document.querySelector(targetId);
                    if (target) {
                        e.preventDefault();
                        const headerOffset = 100;
                        const elementPosition = target.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        })();
    </script>

    @stack('scripts')

</body>
</html>
