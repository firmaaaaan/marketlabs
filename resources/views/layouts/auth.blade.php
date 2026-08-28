<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Autentikasi') — MarketLabs</title>
    <meta name="description" content="Sistem Informasi MarketLabs - platform manajemen laboratorium yang modern, cepat, dan terintegrasi.">

    @php $faviconPath = \App\Models\Setting::get('site_favicon'); @endphp
    <link rel="icon" href="{{ $faviconPath ? asset('storage/' . $faviconPath) : asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @php $siteLogo = \App\Models\Setting::get('site_logo'); @endphp

    <div class="flex min-h-screen">
        {{-- Panel branding (kiri) — tampil di layar besar --}}
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-emerald-800 via-emerald-700 to-emerald-900 p-12 lg:flex xl:p-16">
            {{-- Dekorasi --}}
            <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute top-1/3 -left-32 h-80 w-80 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 right-1/4 h-72 w-72 rounded-full bg-emerald-300/10 blur-3xl"></div>

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="relative flex items-center gap-3">
                @if ($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo MarketLabs" class="h-14 w-14 object-contain">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-lg font-bold text-emerald-700 shadow-lg">M</span>
                @endif
                <div>
                    <span class="text-2xl font-bold tracking-tight text-white">
                        Market<span class="text-emerald-300">Labs</span>
                    </span>
                    <p class="text-xs font-semibold text-emerald-300/80">by UPT Laboratorium Terpadu</p>
                </div>
            </a>

            {{-- Tagline --}}
            <div class="relative">
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-white xl:text-5xl">
                    Kelola Riset, Alat &amp;<br>Pemeriksaan dalam
                    <span class="text-emerald-300">Satu Platform</span>
                </h1>
                <p class="mt-5 max-w-md text-base leading-relaxed text-emerald-100/85">
                    MarketLabs membantu Anda meminjam alat laboratorium, mengajukan riset &amp; pengujian sampel, hingga membooking pemeriksaan kesehatan — cepat dan transparan.
                </p>

                <ul class="mt-10 space-y-4">
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-white/15 backdrop-blur">
                            <svg class="h-5 w-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-white">Peminjaman alat laboratorium lengkap</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-white/15 backdrop-blur">
                            <svg class="h-5 w-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-white">Riset, pengujian sampel &amp; logbook</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-white/15 backdrop-blur">
                            <svg class="h-5 w-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3.75V7.5m0 0v3.75m-3-3.75h6" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-white">Booking pemeriksaan kesehatan</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-white/15 backdrop-blur">
                            <svg class="h-5 w-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-white">Antrian real-time &amp; hasil terpusat</span>
                    </li>
                </ul>
            </div>

            <p class="relative text-sm text-emerald-200/70">
                &copy; {{ date('Y') }} Sistem Informasi MarketLabs. Semua hak dilindungi.
            </p>
        </div>

        {{-- Panel form (kanan) --}}
        <div class="flex w-full flex-col items-center justify-center px-4 py-12 sm:px-6 lg:w-1/2 lg:px-12">
            {{-- Logo (mobile) --}}
            <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2 lg:hidden">
                @if ($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo MarketLabs" class="h-14 w-14 object-contain">
                    @else
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-600 text-lg font-bold text-white shadow-lg">M</span>
                    @endif
                <div>
                    <span class="text-2xl font-bold tracking-tight text-slate-900">
                        Market<span class="text-emerald-600">Labs</span>
                    </span>
                    <p class="text-xs font-semibold text-slate-500">by UPT Laboratorium Terpadu</p>
                </div>
            </a>

            <div class="w-full max-w-md">
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60 sm:p-10">
                    @yield('card')
                </div>

                <p class="mt-6 text-center text-sm text-slate-400 lg:hidden">
                    &copy; {{ date('Y') }} Sistem Informasi MarketLabs
                </p>
            </div>
        </div>
    </div>

</body>
</html>
