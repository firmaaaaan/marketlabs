@extends('layouts.app')

@section('title', 'Notifikasi - MarketLabs')

@section('content')

<section class="pt-32 pb-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Notifikasi</h1>
                <p class="mt-2 text-slate-600">Seluruh pemberitahuan terkait peminjaman alat Anda.</p>
            </div>
            @if ($notifications->isNotEmpty())
                <form action="{{ route('notifications.read') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-8 space-y-3">
            @forelse ($notifications as $n)
                @php
                    $url = $n->data['url'] ?? route('borrowings.index');
                    $title = $n->data['title'] ?? 'Notifikasi';
                    $message = $n->data['message'] ?? null;
                    $unread = $n->read_at === null;
                @endphp
                <a href="{{ $url }}"
                   class="flex items-start gap-4 rounded-2xl border p-5 shadow-sm transition hover:border-emerald-200 hover:shadow {{ $unread ? 'border-emerald-200 bg-emerald-50/50' : 'border-slate-200 bg-white' }}">
                    <span class="mt-1.5 h-2.5 w-2.5 flex-none rounded-full {{ $unread ? 'bg-emerald-500' : 'bg-slate-200' }}"></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-bold {{ $unread ? 'text-slate-900' : 'text-slate-600' }}">{{ $title }}</span>
                            <span class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                        </span>
                        @if ($message)
                            <span class="mt-1 block text-sm leading-relaxed text-slate-600">{{ $message }}</span>
                        @endif
                        @if ($unread)
                            <span class="mt-2 inline-block rounded-full bg-emerald-600 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                Belum dibaca
                            </span>
                        @endif
                    </span>
                    <svg class="mt-1 h-4 w-4 flex-none text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <h2 class="text-lg font-bold text-slate-900">Belum ada notifikasi</h2>
                    <p class="mt-1 text-sm text-slate-500">Perubahan status peminjaman Anda akan muncul di sini.</p>
                    <a href="{{ route('tools.index') }}"
                       class="mt-6 inline-block rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                        Pinjam Alat
                    </a>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="mt-10">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</section>

@endsection
