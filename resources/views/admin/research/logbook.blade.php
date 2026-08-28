@extends('layouts.admin')

@section('title', 'Logbook Penelitian - MarketLabs')

@section('page', 'Permohonan Riset')

@section('content')

<a href="{{ route('admin.research.show', $proposal) }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
    ← Kembali ke Detail Permohonan
</a>

<div class="mt-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Logbook Penelitian</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $proposal->code }} — {{ $proposal->title }}</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-sm font-semibold text-slate-500">Pemohon: {{ $proposal->user->name }}</span>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $proposal->logbooks->count() }} entri</span>
        <a href="{{ route('admin.research.logbook.print', $proposal) }}" target="_blank"
           class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700">
            Cetak
        </a>
    </div>
</div>

<div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    @forelse ($proposal->logbooks as $logbook)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-3 font-semibold">No</th>
                        <th class="px-6 py-3 font-semibold">Tanggal</th>
                        <th class="px-6 py-3 font-semibold">Catatan Kegiatan</th>
                        <th class="px-6 py-3 font-semibold">Kendala</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($proposal->logbooks as $logbook)
                        <tr>
                            <td class="px-6 py-4 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-900">{{ $logbook->log_date->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4 text-slate-700">
                                <p class="whitespace-pre-line">{{ $logbook->note }}</p>
                                <p class="mt-1 text-xs text-slate-400">Ditambahkan {{ $logbook->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if ($logbook->obstacle)
                                    <p class="whitespace-pre-line text-amber-700">{{ $logbook->obstacle }}</p>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="px-6 py-10 text-center">
            <p class="text-sm text-slate-500">Belum ada entri logbook dari pemohon.</p>
        </div>
    @endforelse
</div>

@endsection
