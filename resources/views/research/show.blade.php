@php
    $isOwner = $proposal->user_id === auth()->id();
    $isMember = \App\Models\ResearchProposalMember::where('research_proposal_id', $proposal->id)
        ->where('user_id', auth()->id())
        ->exists();
@endphp

@extends(($isOwner || $isMember) ? 'layouts.app' : 'layouts.staff')

@section('title', $proposal->title . ' - MarketLabs')
@section('page', 'Detail Riset')

@section('content')

<section class="{{ $isOwner ? 'py-16' : 'py-2' }}">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <a href="{{ $isOwner ? route('research.index') : route('laboran.index') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
            ← Kembali{{ $isOwner ? ' ke Riwayat Riset' : ' ke Halaman Laboran' }}
        </a>

        @if (session('success'))
            <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-bold text-slate-500">{{ $proposal->code }}</span>
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
            @if ($isOwner || $isMember)
                <div class="flex items-center gap-2">
                    <a href="{{ route('research.logbook', $proposal) }}"
                       class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700">
                        Logbook Penelitian
                    </a>
                    @if ($isOwner && in_array($proposal->status, ['pending', 'approved']))
                        <form action="{{ route('research.cancel', $proposal) }}" method="POST"
                              data-confirm="Batalkan permohonan {{ $proposal->code }}?" data-confirm-accept="Ya, Batalkan">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                                Batalkan Permohonan
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">{{ $proposal->title }}</h1>

        {{-- Informasi Pemohon --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Informasi Pemohon</h2>
            </div>
            <div class="grid gap-6 px-6 py-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->user->email }}</p>
                </div>
                @if ($proposal->nim_nip)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">NIM / NIP / NIDN / NIK</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->nim_nip }}</p>
                    </div>
                @endif
                @if ($proposal->institution)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Instansi</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->institution }}</p>
                    </div>
                @endif
                @if ($proposal->customer_type)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Customer</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->customer_type_label }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Diajukan Pada</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>
        {{-- Anggota Penelitian --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Anggota Penelitian</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ $proposal->members->count() }} anggota tim penelitian.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="w-14 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">No</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Peran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($proposal->members as $index => $member)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                                    @if ($member->user)
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $member->user->email }}{{ $member->user->nim_nip ? ' · ' . $member->user->nim_nip : '' }}{{ $member->user->institution ? ' · ' . $member->user->institution : '' }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $member->role ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-500">Tidak ada anggota tercantum.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Detail Riset --}}
        <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Detail Permohonan</h2>
            </div>
            <div class="grid gap-6 px-6 py-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bidang Penelitian</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->field ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Periode Penelitian</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">
                        @if ($proposal->start_date && $proposal->end_date)
                            {{ $proposal->start_date->translatedFormat('d M Y') }} — {{ $proposal->end_date->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                @if ($proposal->bench_fee !== null)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bench Fee Laboratorium</p>
                        <p class="mt-1 text-sm font-bold text-emerald-700">{{ $proposal->formatted_bench_fee }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $proposal->bench_fee_level }} · Instansi {{ $proposal->bench_fee_type_label }} · Kategori {{ $proposal->bench_fee_category_label }} · tarif per 3 bulan
                        </p>
                    </div>
                @endif
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Membutuhkan Laboran</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $proposal->needs_laboran ? 'Ya' : 'Tidak' }}</p>
                    @if ($proposal->laboran)
                        <p class="mt-1 text-xs text-slate-500">Laboran: {{ $proposal->laboran->name }}</p>
                    @endif
                    @if ($proposal->laboratorium)
                        <p class="mt-1 text-xs text-slate-500">Laboratorium: {{ $proposal->laboratorium->name }}</p>
                    @endif
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Deskripsi Penelitian</p>
                    <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $proposal->description ?? '-' }}</p>
                </div>
                @if ($proposal->objectives)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tujuan Penelitian</p>
                        <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $proposal->objectives }}</p>
                    </div>
                @endif
                @if ($proposal->document_path)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dokumen Pendukung</p>
                        <a href="{{ route('research.document', [$proposal, 'document']) }}" target="_blank"
                           class="mt-1 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            📄 {{ $proposal->document_name }}
                        </a>
                    </div>
                @endif
                @if ($proposal->letter_path)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Surat Permohonan</p>
                        <a href="{{ route('research.document', [$proposal, 'letter']) }}" target="_blank"
                           class="mt-1 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            📄 {{ $proposal->letter_name }}
                        </a>
                    </div>
                @endif
                @if ($proposal->replacement_letter_path)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Surat Penggantian Alat</p>
                        <a href="{{ route('research.document', [$proposal, 'replacement']) }}" target="_blank"
                           class="mt-1 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            📄 {{ $proposal->replacement_letter_name }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Alat yang Dibutuhkan: tabel + subtotal & total --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Alat yang Dibutuhkan</h2>
                <p class="mt-0.5 text-xs text-slate-500">Subtotal dihitung dari harga/hari × jumlah × hari penggunaan per alat.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alat</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode / Kategori</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Hari</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Harga / Hari</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Subtotal Sewa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($proposal->tools as $tool)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $tool->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $tool->code }} · {{ $tool->category_name }}</td>
                                <td class="px-6 py-4 text-right text-slate-600">{{ $tool->pivot->quantity }} unit</td>
                                <td class="px-6 py-4 text-right text-slate-600">{{ $tool->pivot->days }}</td>
                                <td class="px-6 py-4 text-right text-slate-600">Rp {{ number_format($tool->price_per_day, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-slate-900">Rp {{ number_format($tool->price_per_day * $tool->pivot->quantity * $tool->pivot->days, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">Tidak ada alat yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan biaya --}}
            <div class="space-y-2 border-t border-slate-100 px-6 py-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Total Sewa Alat</span>
                    <span class="font-semibold text-slate-900">{{ $proposal->formatted_tools_subtotal }}</span>
                </div>
                @if ($proposal->bench_fee !== null)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Bench Fee Laboratorium</span>
                        <span class="font-semibold text-slate-900">{{ $proposal->formatted_bench_fee }}</span>
                    </div>
                @endif
                @if ($proposal->laboran_fee !== null)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Biaya Laboran</span>
                        <span class="font-semibold text-slate-900">{{ $proposal->formatted_laboran_fee }}</span>
                    </div>
                @endif
                @if ($proposal->penalty > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Denda / Biaya Tambahan</span>
                        <span class="font-semibold text-amber-600">{{ $proposal->formatted_penalty }}</span>
                    </div>
                    @if ($proposal->penalty_note)
                        <p class="mt-1 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700">{{ $proposal->penalty_note }}</p>
                    @endif
                @endif
                @if ($proposal->bench_fee !== null || $proposal->laboran_fee !== null || $proposal->tools->isNotEmpty())
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Status Pembayaran</span>
                        <span class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $proposal->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $proposal->payment_status_label }}
                            </span>
                            @if ($proposal->invoice_number && $isOwner)
                                <a href="{{ route('research.invoice', $proposal) }}"
                                   class="text-xs font-semibold text-emerald-600 transition hover:underline">
                                    Lihat Invoice
                                </a>
                            @endif
                        </span>
                    </div>
                @endif
                <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-base font-bold text-emerald-700">
                    <span>Total Keseluruhan</span>
                    <span>{{ $proposal->formatted_grand_total }}</span>
                </div>
            </div>
        </div>

        {{-- Catatan Admin --}}
        @if ($proposal->admin_notes)
            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6">
                <h2 class="text-sm font-bold text-amber-800">Catatan Admin</h2>
                <p class="mt-2 text-sm whitespace-pre-line text-amber-900">{{ $proposal->admin_notes }}</p>
            </div>
        @endif

    </div>
</section>

@endsection
