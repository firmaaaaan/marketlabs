@extends('layouts.admin')

@section('title', 'Detail Event - MarketLabs')

@section('page', 'Detail Event')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $event->title }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ $event->code }} · {{ $event->status_label }}
            @if ($event->mode)
                <span class="ml-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ match ($event->mode) {
                    \App\Models\Event::MODE_ONLINE => 'bg-sky-50 text-sky-700',
                    \App\Models\Event::MODE_OFFLINE => 'bg-amber-50 text-amber-700',
                    \App\Models\Event::MODE_HYBRID => 'bg-violet-50 text-violet-700',
                    default => 'bg-slate-100 text-slate-500',
                } }}">
                    {{ $event->mode_label }}
                </span>
            @endif
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.events.index') }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
            ← Kembali
        </a>
        <a href="{{ route('admin.events.edit', $event) }}"
           class="rounded-lg border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
            Edit
        </a>
        <form action="{{ route('admin.events.certificate.generate', $event) }}" method="POST" class="inline">
            @csrf
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                Generate Sertifikat Hadir
            </button>
        </form>
        <a href="{{ route('admin.events.export-participants', $event) }}"
           class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
            Export Peserta
        </a>
        <a href="{{ route('admin.events.export-attendance', $event) }}"
           class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
            Export Presensi
        </a>
    </div>
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-sm font-bold text-red-700">Periksa kembali isian Anda:</p>
        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Info ringkas --}}
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Peserta Terdaftar</p>
        <p class="mt-1 text-2xl font-extrabold text-slate-900">
            {{ $event->registrations()->whereIn('status', [\App\Models\EventRegistration::STATUS_PENDING, \App\Models\EventRegistration::STATUS_REGISTERED])->count() }}
            @if ($event->quota)
                <span class="text-sm font-semibold text-slate-400">/ {{ $event->quota }}</span>
            @endif
        </p>
    </div>
    @if ($event->attendance_enabled)
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sudah Hadir</p>
        <p class="mt-1 text-2xl font-extrabold text-emerald-600">
            {{ $event->registrations()->whereNotNull('attended_at')->count() }}
        </p>
    </div>
    @endif
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sertifikat Dibuat</p>
        <p class="mt-1 text-2xl font-extrabold text-sky-600">
            {{ $event->registrations()->whereNotNull('certificate_number')->count() }}
        </p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Jadwal</p>
        <p class="mt-1 text-sm font-bold text-slate-700">
            {{ $event->starts_at?->translatedFormat('d M Y H:i') ?? 'Belum dijadwalkan' }}
        </p>
        <p class="mt-0.5 text-xs text-slate-500">{{ $event->location ?? '-' }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Biaya Pendaftaran</p>
        @if ($event->has_fee)
            @if ($event->has_discount)
                <p class="mt-1 text-xs text-slate-400 line-through">{{ $event->fee_label }}</p>
                <p class="mt-0.5 text-lg font-extrabold text-emerald-700">{{ $event->effective_fee_label }}</p>
                <p class="text-[11px] font-semibold text-red-600">Diskon {{ $event->discount_label }}</p>
            @else
                <p class="mt-1 text-lg font-extrabold text-slate-900">{{ $event->fee_label }}</p>
            @endif
        @else
            <p class="mt-1 text-lg font-extrabold text-emerald-600">Gratis</p>
        @endif
    </div>
</div>

{{-- Poster & thumbnail event --}}
@if ($event->poster || $event->image)
    <div class="mt-6 flex flex-wrap gap-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if ($event->poster)
            <div class="flex-1">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Poster</p>
                <img src="{{ \Illuminate\Support\Facades\Storage::url($event->poster) }}" alt="Poster {{ $event->title }}"
                     class="mt-2 max-h-96 w-auto rounded-lg object-cover">
            </div>
        @endif
        @if ($event->image)
            <div class="flex-1">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Thumbnail</p>
                <img src="{{ \Illuminate\Support\Facades\Storage::url($event->image) }}" alt="Thumbnail {{ $event->title }}"
                     class="mt-2 h-48 w-full rounded-lg object-cover">
            </div>
        @endif
    </div>
@endif

{{-- Pengaturan sertifikat --}}
@if (! $event->certificate_ready)
    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50/60 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Sertifikat belum siap</h2>
                <p class="mt-0.5 text-sm text-slate-600">Unggah template master &amp; atur tata letak nama sebelum bisa generate sertifikat.</p>
            </div>
            <a href="{{ route('admin.events.certificate', $event) }}"
               class="rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700">
                Atur Sertifikat
            </a>
        </div>
    </div>
@else
    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Template &amp; tata letak sertifikat siap</h2>
                <p class="mt-0.5 text-sm text-slate-600">Ubah tata letak nama / template kapan saja.</p>
            </div>
            <a href="{{ route('admin.events.certificate', $event) }}"
               class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Edit Sertifikat
            </a>
        </div>
    </div>
@endif

{{-- Daftar peserta --}}
<div class="mt-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-lg font-extrabold text-slate-900">Daftar Peserta</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    '' => 'Semua',
                    \App\Models\EventRegistration::STATUS_PENDING => 'Menunggu Konfirmasi',
                    \App\Models\EventRegistration::STATUS_REGISTERED => 'Terdaftar',
                    \App\Models\EventRegistration::STATUS_CANCELLED => 'Dibatalkan',
                ] as $value => $label)
                    <a href="{{ route('admin.events.show', ['event' => $event, 'status' => $value]) }}"
                       class="rounded-full px-4 py-1.5 text-xs font-semibold transition {{ request('status') === $value ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-emerald-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
        <div id="bulkActions" class="hidden">
            <form id="bulkForm" method="POST" action="{{ route('admin.events.registrations.bulk-status', $event) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="bulkStatus" value="">
                <input type="hidden" name="registration_ids" id="bulkIds" value="">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600" id="bulkCount">0 dipilih</span>
                    <button type="submit" onclick="document.getElementById('bulkStatus').value='registered'"
                            class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                        Terima Terpilih
                    </button>
                    <button type="button" onclick="bulkAction('cancelled')"
                            class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                        Batalkan Terpilih
                    </button>
                    <button type="button" onclick="bulkAction('pending')"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                        Kembalikan Terpilih
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-10">
                            <input type="checkbox" id="selectAll" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">NIM/NIP</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Instansi</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Hadir</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Sertifikat</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($registrations as $i => $registration)
                        {{-- Baris utama --}}
                        <tr class="detail-toggle cursor-pointer hover:bg-slate-50/50" data-target="detail-{{ $registration->id }}">
                            <td class="whitespace-nowrap px-4 py-3" onclick="event.stopPropagation()">
                                <input type="checkbox" name="registration_ids[]" value="{{ $registration->id }}"
                                       class="reg-checkbox h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">
                                {{ ($registrations->currentPage() - 1) * $registrations->perPage() + $i + 1 }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <p class="text-sm font-semibold text-slate-900">{{ $registration->user->name }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                {{ $registration->user->email }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                {{ $registration->user->nim_nip ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                {{ $registration->user->institution ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ match($registration->status) {
                                    \App\Models\EventRegistration::STATUS_PENDING => 'bg-amber-50 text-amber-700',
                                    \App\Models\EventRegistration::STATUS_REGISTERED => 'bg-emerald-50 text-emerald-700',
                                    \App\Models\EventRegistration::STATUS_CANCELLED => 'bg-slate-100 text-slate-500',
                                    default => 'bg-slate-100 text-slate-500',
                                } }}">
                                    {{ $registration->status_label }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $registration->is_attended ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $registration->is_attended ? 'Ya' : 'Belum' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($registration->has_certificate)
                                    <a href="{{ route('events.certificate', $registration) }}" target="_blank"
                                       class="text-xs font-semibold text-sky-600 hover:underline" onclick="event.stopPropagation()">
                                        {{ $registration->certificate_number }}
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.events.registrations.status', [$event, $registration]) }}" method="POST" onclick="event.stopPropagation()">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold transition {{ match($registration->status) {
                                                    \App\Models\EventRegistration::STATUS_PENDING => 'text-emerald-600 border-emerald-200 hover:bg-emerald-50',
                                                    \App\Models\EventRegistration::STATUS_REGISTERED => 'text-red-600 border-red-200 hover:bg-red-50',
                                                    \App\Models\EventRegistration::STATUS_CANCELLED => 'text-amber-600 border-amber-200 hover:bg-amber-50',
                                                    default => 'text-slate-600',
                                                } }}">
                                            {{ match($registration->status) {
                                                \App\Models\EventRegistration::STATUS_PENDING => 'Terima',
                                                \App\Models\EventRegistration::STATUS_REGISTERED => 'Batalkan',
                                                \App\Models\EventRegistration::STATUS_CANCELLED => 'Kembalikan',
                                                default => 'Ubah',
                                            } }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Baris detail (sembunyi secara default) --}}
                        <tr id="detail-{{ $registration->id }}" class="detail-row hidden">
                            <td colspan="10" class="bg-slate-50/80 px-6 py-4">
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    {{-- Link presensi --}}
                                    @if ($event->attendance_enabled && $registration->attendance_token)
                                        <div>
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Link Presensi</p>
                                            <a href="{{ route('events.attendance', $registration->attendance_token) }}" target="_blank"
                                               class="mt-1 inline-block text-xs font-semibold text-emerald-600 hover:underline">
                                                {{ route('events.attendance', $registration->attendance_token) }}
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Isian registrasi --}}
                                    @if ($registration->answers)
                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Isian Registrasi</p>
                                            <div class="mt-1.5 space-y-3 text-sm text-slate-700">
                                                @foreach ($registration->answers as $key => $value)
                                                    @php
                                                        $isImage = is_string($value) && preg_match('/\.(jpe?g|png|gif|webp|bmp|svg)$/i', $value);
                                                        $isFile = is_string($value) && str_contains($value, '/') && ! $isImage;
                                                        $fileUrl = $isImage || $isFile ? route('events.answer-file', [$registration, $key]) : '';
                                                        $fileName = $isFile || $isImage ? basename($value) : '';
                                                    @endphp
                                                    @if ($isImage)
                                                        <div>
                                                            <p class="text-xs font-semibold text-slate-500 mb-1">{{ $key }}</p>
                                                            <div class="rounded-lg border border-slate-200 bg-white p-1">
                                                                <img src="{{ $fileUrl }}" alt="{{ $key }}"
                                                                     class="max-h-48 w-auto rounded object-contain">
                                                            </div>
                                                        </div>
                                                    @elseif ($isFile)
                                                        <div onclick="event.stopPropagation()">
                                                            <p class="text-xs font-semibold text-slate-500 mb-1">{{ $key }}</p>
                                                            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                                                                <div class="flex items-center border-b border-slate-100 px-3 py-2">
                                                                    <span class="text-xs font-semibold text-slate-600">📄 {{ $fileName }}</span>
                                                                </div>
                                                                <iframe src="{{ $fileUrl }}"
                                                                        class="w-full border-0"
                                                                        style="height: 500px;">
                                                                </iframe>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <p><span class="font-semibold text-slate-500">{{ $key }}:</span> {{ $value }}</p>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Presensi terisi --}}
                                    @if ($event->attendance_enabled && $registration->is_attended && $registration->attendance_answers)
                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">
                                                Presensi · {{ $registration->attended_at->translatedFormat('d M Y H:i') }}
                                            </p>
                                            <div class="mt-1.5 grid gap-1 text-sm text-slate-700 sm:grid-cols-2 lg:grid-cols-3">
                                                @foreach ($registration->attendance_answers as $key => $value)
                                                    <p><span class="font-semibold text-slate-500">{{ $key }}:</span> {{ $value }}</p>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Form presensi manual --}}
                                    @if ($event->attendance_enabled && $event->attendance_fields && $registration->status === \App\Models\EventRegistration::STATUS_REGISTERED && ! $registration->is_attended)
                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <form action="{{ route('admin.events.registrations.attendance', [$event, $registration]) }}" method="POST"
                                                  class="rounded-xl border border-dashed border-emerald-300 bg-emerald-50/50 p-4">
                                                @csrf
                                                @method('PATCH')
                                                <p class="text-xs font-bold text-emerald-700">Catat Presensi Manual</p>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                    @foreach (\App\Support\FormFields::normalize($event->attendance_fields) as $field)
                                                        @if ($field['type'] === 'textarea')
                                                            <div class="sm:col-span-2">
                                                                <label class="block text-xs font-semibold text-slate-600">{{ $field['label'] }}</label>
                                                                <textarea name="{{ $field['key'] }}" rows="2"
                                                                          class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"></textarea>
                                                            </div>
                                                        @elseif ($field['type'] === 'select')
                                                            <div>
                                                                <label class="block text-xs font-semibold text-slate-600">{{ $field['label'] }}</label>
                                                                <select name="{{ $field['key'] }}"
                                                                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                                    <option value="">-- Pilih --</option>
                                                                    @foreach ($field['options'] as $option)
                                                                        <option value="{{ $option }}">{{ $option }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @elseif ($field['type'] === 'checkbox')
                                                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                                                                <input type="checkbox" name="{{ $field['key'] }}" value="1"
                                                                       class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                                {{ $field['label'] }}
                                                            </label>
                                                        @else
                                                            <div>
                                                                <label class="block text-xs font-semibold text-slate-600">{{ $field['label'] }}</label>
                                                                <input type="{{ $field['type'] === 'number' ? 'number' : ($field['type'] === 'date' ? 'date' : 'text') }}"
                                                                       name="{{ $field['key'] }}"
                                                                       class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                <button type="submit"
                                                        class="mt-3 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                    Tandai Hadir
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center">
                                <p class="text-lg font-semibold text-slate-700">Belum ada peserta</p>
                                <p class="mt-1 text-sm text-slate-500">Link event tersedia untuk umum saat status aktif.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($registrations->hasPages())
        <div class="mt-6">
            {{ $registrations->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
// Expand/collapse detail rows
document.querySelectorAll('.detail-toggle').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('form') || e.target.closest('input')) return;
        var targetId = row.getAttribute('data-target');
        var detail = document.getElementById(targetId);
        if (detail) detail.classList.toggle('hidden');
    });
});

// Bulk selection
var selectAll = document.getElementById('selectAll');
var checkboxes = document.querySelectorAll('.reg-checkbox');
var bulkActions = document.getElementById('bulkActions');
var bulkCount = document.getElementById('bulkCount');
var bulkIds = document.getElementById('bulkIds');

function updateBulkUI() {
    var checked = document.querySelectorAll('.reg-checkbox:checked');
    var count = checked.length;
    bulkCount.textContent = count + ' dipilih';
    bulkActions.classList.toggle('hidden', count === 0);

    var ids = [];
    checked.forEach(function (cb) { ids.push(cb.value); });
    bulkIds.value = JSON.stringify(ids).replace(/[\[\]]/g, '');
}

selectAll.addEventListener('change', function () {
    checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
    updateBulkUI();
});

checkboxes.forEach(function (cb) {
    cb.addEventListener('change', updateBulkUI);
});

function bulkAction(status) {
    document.getElementById('bulkStatus').value = status;
    document.getElementById('bulkForm').submit();
}
</script>
@endpush