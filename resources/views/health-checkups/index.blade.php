@extends('layouts.account')

@section('title', 'Booking Pemeriksaan Kesehatan - MarketLabs')

@section('account-content')

<div>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pemeriksaan Kesehatan</h1>
            <p class="mt-1 text-sm text-slate-600">Riwayat booking pemeriksaan Anda beserta status dan hasilnya.</p>
        </div>
        <a href="{{ route('health-checkups.catalog') }}"
           class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
            + Booking Pemeriksaan
        </a>
    </div>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-8 space-y-6">
            @forelse ($checkups as $checkup)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-xs font-bold text-slate-500">{{ $checkup->code }}</p>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">{{ $checkup->queue_label }}</span>
                            </div>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $checkup->type?->name ?? 'Pemeriksaan' }}</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $checkup->booking_date->translatedFormat('d M Y') }}
                                @if ($checkup->purpose)
                                    · {{ $checkup->purpose }}
                                @endif
                            </p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($checkup->status) {
                            'pending' => 'bg-amber-50 text-amber-700',
                            'approved' => 'bg-sky-50 text-sky-700',
                            'done' => 'bg-emerald-50 text-emerald-700',
                            'rejected' => 'bg-red-50 text-red-600',
                            'cancelled' => 'bg-slate-100 text-slate-500',
                            default => 'bg-slate-100 text-slate-500',
                        } }}">
                            {{ $checkup->status_label }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-4">
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <span class="text-slate-500">Booking {{ $checkup->created_at->diffForHumans() }}</span>
                            @php
                                $q = $queues[$checkup->booking_date->toDateString()][$checkup->id] ?? null;
                            @endphp
                            @if ($q && $q['position'] !== null)
                                <span class="text-xs font-semibold text-sky-700">Posisi: ke-{{ $q['position'] }} dari {{ $q['waiting'] }}</span>
                            @endif
                            <span class="font-semibold text-slate-700">{{ $checkup->formatted_price }}</span>
                            @if ($checkup->result)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                    Hasil: {{ $checkup->result }}
                                </span>
                            @endif
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $checkup->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $checkup->payment_status_label }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if (in_array($checkup->status, ['pending', 'approved']))
                                <form action="{{ route('health-checkups.cancel', $checkup) }}" method="POST"
                                      data-confirm="Batalkan booking {{ $checkup->code }}?" data-confirm-accept="Ya, Batalkan">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                                        Batalkan
                                    </button>
                                </form>
                            @endif
                            @if ($checkup->is_paid)
                                <a href="{{ route('health-checkups.invoice', $checkup) }}" target="_blank"
                                   class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                                    Invoice
                                </a>
                            @endif
                            @if ($checkup->status === 'done')
                                <a href="{{ route('health-checkups.certificate', $checkup) }}" target="_blank"
                                   class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                    🖨 Cetak Hasil
                                </a>
                            @endif
                            @if (! in_array($checkup->status, ['rejected', 'cancelled']))
                                <a href="{{ route('health-checkups.ticket', $checkup) }}" target="_blank"
                                   class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                                    Cetak Antrian
                                </a>
                            @endif
                            <a href="{{ route('health-checkups.show', $checkup) }}"
                               class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Belum ada booking pemeriksaan kesehatan.</p>
                    <a href="{{ route('health-checkups.catalog') }}"
                       class="mt-4 inline-block rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Booking Pemeriksaan Pertama
                    </a>
                </div>
            @endforelse
        </div>

        @if ($checkups->hasPages())
            <div class="mt-10">
                {{ $checkups->links() }}
            </div>
        @endif
    </div>
</div>

@endsection