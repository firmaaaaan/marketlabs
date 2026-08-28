@extends('layouts.app')

@section('title', $borrowing->code . ' - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-slate-500">
            <a href="{{ route('borrowings.index') }}" class="transition hover:text-emerald-600">Riwayat Peminjaman</a>
            <span class="mx-2">/</span>
            <span class="text-slate-700">{{ $borrowing->code }}</span>
        </nav>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $borrowing->code }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Diajukan pada {{ $borrowing->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="rounded-full px-4 py-1.5 text-sm font-semibold {{ match ($borrowing->status) {
                        'pending' => 'bg-amber-50 text-amber-700',
                        'approved' => 'bg-sky-50 text-sky-700',
                        'rejected' => 'bg-red-50 text-red-600',
                        'borrowed' => 'bg-indigo-50 text-indigo-700',
                        'returned' => 'bg-emerald-50 text-emerald-700',
                        'cancelled' => 'bg-slate-100 text-slate-500',
                        default => 'bg-slate-100 text-slate-500',
                    } }}">
                        {{ \App\Models\Borrowing::statusLabel($borrowing->status) }}
                    </span>
                    <span class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $borrowing->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $borrowing->payment_status_label }}
                    </span>
                    <a href="{{ route('borrowings.invoice', $borrowing) }}" target="_blank"
                       class="rounded-lg border border-emerald-200 px-4 py-1.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                        Lihat Invoice
                    </a>
                </div>
            </div>

            <div class="mt-6 grid gap-4 rounded-xl bg-slate-50 p-5 sm:grid-cols-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Jenis Peminjaman</p>
                    <p class="mt-1">
                        <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $borrowing->borrower_type === 'eksternal' ? 'bg-indigo-50 text-indigo-700' : 'bg-emerald-50 text-emerald-700' }}">
                            {{ $borrowing->borrower_type_label }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Tanggal Pinjam</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $borrowing->borrow_date->translatedFormat('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Tanggal Kembali</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $borrowing->return_date->translatedFormat('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Unit</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $borrowing->items->sum('quantity') }} unit</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50/40 p-5">
                <h3 class="text-sm font-bold text-slate-900">Informasi Peminjam</h3>
                <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Nama Lengkap</p>
                        <p class="mt-0.5 font-semibold text-slate-900">{{ $borrowing->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Email</p>
                        <p class="mt-0.5 font-semibold text-slate-900">{{ $borrowing->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">NIM / NIP / NIDN / NIK</p>
                        <p class="mt-0.5 font-semibold text-slate-900">{{ $borrowing->nim_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Instansi</p>
                        <p class="mt-0.5 font-semibold text-slate-900">{{ $borrowing->institution ?? '-' }}</p>
                    </div>
                </div>
            </div>

            @if ($borrowing->document_path)
                <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900">Dokumen Pendukung</p>
                        <p class="truncate text-xs text-slate-500">{{ $borrowing->document_name }}</p>
                    </div>
                    <a href="{{ route('borrowings.document', $borrowing) }}" target="_blank"
                       class="flex-none rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                        Lihat Dokumen
                    </a>
                </div>
            @endif

            <h2 class="mt-8 text-lg font-bold text-slate-900">Daftar Alat</h2>
            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alat</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Harga/Hari</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Subtotal/Hari</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($borrowing->items as $item)
                            <tr>
                                <td class="px-5 py-4 text-sm font-medium text-slate-900">{{ $item->tool->name }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $item->tool->code }}</td>
                                <td class="px-5 py-4 text-right text-sm text-slate-600">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right text-sm font-semibold text-slate-900">{{ $item->quantity }}</td>
                                <td class="px-5 py-4 text-right text-sm font-semibold text-slate-900">Rp {{ number_format($item->price_per_day * $item->quantity, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 rounded-xl bg-emerald-50 p-5">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Lama peminjaman</span>
                    <span class="font-semibold text-slate-900">{{ $borrowing->duration_days }} hari</span>
                </div>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-slate-600">Subtotal biaya</span>
                    <span class="font-semibold text-slate-900">{{ $borrowing->formatted_base_cost }}</span>
                </div>
                @if ($borrowing->discount_amount > 0)
                    <div class="mt-2 flex justify-between text-sm">
                        <span class="text-slate-600">Diskon ({{ $borrowing->discount }}%)</span>
                        <span class="font-semibold text-red-500">− {{ $borrowing->formatted_discount_amount }}</span>
                    </div>
                @endif
                @if ($borrowing->penalty > 0)
                    <div class="mt-2 flex justify-between text-sm">
                        <span class="text-slate-600">Denda keterlambatan/kerusakan</span>
                        <span class="font-semibold text-amber-600">+ {{ $borrowing->formatted_penalty }}</span>
                    </div>
                @endif
                <div class="mt-2 flex justify-between border-t border-emerald-200 pt-2 text-sm">
                    <span class="font-semibold text-slate-800">Total biaya</span>
                    <span class="text-lg font-extrabold text-emerald-700">{{ $borrowing->formatted_total_cost }}</span>
                </div>
            </div>

            @if ($borrowing->purpose)
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-sm font-bold text-slate-900">Tujuan Peminjaman</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-700">{{ $borrowing->purpose }}</p>
                </div>
            @endif

            @if ($borrowing->pickup_notes)
                <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 p-5">
                    <h3 class="text-sm font-bold text-sky-900">Catatan Pengambilan Alat</h3>
                    <p class="mt-1 text-sm leading-relaxed text-sky-800">{{ $borrowing->pickup_notes }}</p>
                </div>
            @endif

            @if ($borrowing->notes)
                <div class="mt-6">
                    <h3 class="text-sm font-bold text-slate-900">Catatan</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $borrowing->notes }}</p>
                </div>
            @endif

            @if (in_array($borrowing->status, ['pending', 'approved']))
                <div class="mt-8 border-t border-slate-100 pt-6">
                    <form action="{{ route('borrowings.cancel', $borrowing) }}" method="POST"
                          data-confirm="Batalkan peminjaman {{ $borrowing->code }}?" data-confirm-accept="Ya, Batalkan">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            Batalkan Peminjaman
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</section>

@endsection
