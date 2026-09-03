@extends('layouts.app')

@php
    $accountRoute = request()->route() ? request()->route()->getName() : '';
    $activeMenu = match (true) {
        str_starts_with($accountRoute, 'borrowings') => 'borrowings',
        str_starts_with($accountRoute, 'research') => 'research',
        str_starts_with($accountRoute, 'sample-tests') => 'sample-tests',
        str_starts_with($accountRoute, 'health-checkups') => 'health-checkups',
        str_starts_with($accountRoute, 'events') => 'events',
        str_starts_with($accountRoute, 'calendar') => 'calendar',
        default => 'profile',
    };
@endphp

@section('content')
<section class="pt-28 pb-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-8 lg:flex-row">
            {{-- Kolom menu --}}
            <aside class="w-full flex-none lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:w-64 lg:self-start lg:overflow-y-auto">
                <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    <nav class="space-y-1">
                        <a href="{{ route('profile.show') }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeMenu === 'profile' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Profil
                        </a>

                        <p class="px-4 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Riwayat</p>

                        <a href="{{ route('events.my') }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeMenu === 'events' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            Riwayat Event
                        </a>

                        <a href="{{ route('borrowings.index') }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeMenu === 'borrowings' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 17l8 4m8-4l-8 4" />
                            </svg>
                            Peminjaman Alat
                        </a>

                        <a href="{{ route('research.index') }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeMenu === 'research' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Riset &amp; Penelitian
                        </a>

                        <a href="{{ route('sample-tests.index') }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeMenu === 'sample-tests' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Pengujian Sampel
                        </a>

                        <a href="{{ route('health-checkups.index') }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeMenu === 'health-checkups' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3.75V7.5m0 0v3.75m-3-3.75h6" />
                            </svg>
                            Pemeriksaan Kesehatan
                        </a>

                        <a href="{{ route('calendar.index') }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeMenu === 'calendar' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }}">
                            <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15zm0 2.25h.008v.008H16.5v-.008z" />
                            </svg>
                            Kalender
                        </a>
                    </nav>
                </div>
            </aside>

            {{-- Kolom konten --}}
            <div class="min-w-0 flex-1">
                @yield('account-content')
            </div>
        </div>
    </div>
</section>
@endsection
