@extends('layouts.app')

@section('title', 'Logbook Penelitian - ' . $proposal->title)

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('research.show', $proposal) }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
            ← Kembali ke Detail Permohonan
        </a>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Logbook Penelitian</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $proposal->code }} — {{ $proposal->title }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match ($proposal->status) {
                'pending' => 'bg-amber-50 text-amber-700',
                'approved' => 'bg-sky-50 text-sky-700',
                'ongoing' => 'bg-indigo-50 text-indigo-700',
                'rejected' => 'bg-red-50 text-red-600',
                'done' => 'bg-emerald-50 text-emerald-700',
                'cancelled' => 'bg-slate-100 text-slate-500',
                default => 'bg-slate-100 text-slate-500',
            } }}">
                {{ $proposal->status_label }}
            </span>
        </div>

        <div class="mt-8 space-y-6">
            {{-- Form tambah entri (hanya saat sedang berlangsung) --}}
            @if ($proposal->status === \App\Models\ResearchProposal::STATUS_ONGOING)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-bold text-slate-900">Tambah Entri</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Catat kegiatan &amp; kendala selama penelitian berlangsung.</p>
                    </div>
                    <form action="{{ route('research.logbook.store', $proposal) }}" method="POST" class="space-y-4 px-6 py-6">
                        @csrf

                        <div>
                            <label for="log_date" class="block text-sm font-semibold text-slate-700">Tanggal Kegiatan</label>
                            <input type="date" id="log_date" name="log_date" value="{{ old('log_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required
                                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 sm:w-64">
                            @error('log_date')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="note" class="block text-sm font-semibold text-slate-700">Catatan Kegiatan</label>
                            <textarea id="note" name="note" rows="3" required placeholder="Apa yang dikerjakan hari ini?" class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('note') }}</textarea>
                            @error('note')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="obstacle" class="block text-sm font-semibold text-slate-700">Kendala <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                            <textarea id="obstacle" name="obstacle" rows="2" placeholder="Kendala yang dihadapi, jika ada" class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('obstacle') }}</textarea>
                            @error('obstacle')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Tambah Entri
                        </button>
                    </form>
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-5">
                    <p class="text-sm text-slate-600">
                        Logbook hanya dapat diisi saat penelitian berstatus <span class="font-semibold text-indigo-700">Sedang Berlangsung</span>.
                    </p>
                </div>
            @endif

            {{-- Daftar entri --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                    <h2 class="text-lg font-bold text-slate-900">Riwayat Entri</h2>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $proposal->logbooks->count() }} entri</span>
                        <a href="{{ route('research.logbook.print', $proposal) }}" target="_blank"
                           class="rounded-lg border border-slate-200 px-4 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700">
                            Cetak
                        </a>
                    </div>
                </div>

                @forelse ($proposal->logbooks as $logbook)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                                    <th class="px-6 py-3 font-semibold">No</th>
                                    <th class="px-6 py-3 font-semibold">Tanggal</th>
                                    <th class="px-6 py-3 font-semibold">Catatan Kegiatan</th>
                                    <th class="px-6 py-3 font-semibold">Kendala</th>
                                    <th class="px-6 py-3 text-right font-semibold">Aksi</th>
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
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <form action="{{ route('research.logbook.destroy', [$proposal, $logbook]) }}" method="POST"
                                                  data-confirm="Hapus entri logbook ini?" data-confirm-accept="Ya, Hapus">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-500 transition hover:text-red-600">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm text-slate-500">Belum ada entri logbook.</p>
                        @if ($proposal->status === \App\Models\ResearchProposal::STATUS_ONGOING)
                            <p class="mt-1 text-xs text-slate-400">Tambahkan entri pertama melalui form di atas.</p>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

@endsection
