@extends('layouts.admin')

@section('title', 'Jadwal Layanan - MarketLabs')

@section('page', 'Jadwal Layanan')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Jadwal Layanan Pemeriksaan Kesehatan</h1>
        <p class="mt-1 max-w-3xl text-sm text-slate-600">
            Atur jam operasional, kuota, dan penugasan pemeriksa. Penugasan memakai pola per hari
            (mis. Senin otomatis memakai pemeriksa yang sama untuk semua Senin di bulan itu) dan diperbarui sebulan sekali.
        </p>
    </div>
    <a href="{{ route('admin.health-checkups.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
        Kembali ke Booking
    </a>
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
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

<div class="mt-8 grid gap-6 xl:grid-cols-5">
    {{-- Kiri: pengaturan umum --}}
    <form action="{{ route('admin.schedule.update') }}" method="POST" class="space-y-6 xl:col-span-2">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
            <h2 class="text-base font-bold text-slate-900">Pengaturan Umum</h2>
            <p class="mt-0.5 text-sm text-slate-600">Jam layanan, kuota, durasi, dan penugasan otomatis.</p>

            <div class="mt-6 space-y-6">
                <label class="flex cursor-pointer items-start justify-between gap-4">
                    <span>
                        <span class="block text-sm font-bold text-slate-900">Aktifkan pembatasan jadwal</span>
                        <span class="mt-0.5 block text-xs text-slate-600">Nonaktif = semua hari terbuka tanpa kuota.</span>
                    </span>
                    <span class="relative inline-flex flex-none items-center">
                        <input type="checkbox" name="schedule_enabled" value="1" class="peer sr-only" {{ $enabled ? 'checked' : '' }}>
                        <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-emerald-600 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start justify-between gap-4">
                    <span>
                        <span class="block text-sm font-bold text-slate-900">Penugasan pemeriksa otomatis</span>
                        <span class="mt-0.5 block text-xs text-slate-600">Booking otomatis ditugaskan ke pemeriksa (laboran) hari itu, dibagi rata secara acak.</span>
                    </span>
                    <span class="relative inline-flex flex-none items-center">
                        <input type="checkbox" name="schedule_auto_assign" value="1" class="peer sr-only" {{ $autoAssign ? 'checked' : '' }}>
                        <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-emerald-600 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                    </span>
                </label>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Hari Operasional</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ([1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-1.5 rounded-xl border px-3 py-2 text-sm transition {{ in_array($value, $days) ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-emerald-300' }}">
                                <input type="checkbox" name="days[]" value="{{ $value }}" {{ in_array($value, $days) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="font-medium text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('days')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="open_time" class="block text-sm font-semibold text-slate-700">Jam Buka</label>
                        <input type="time" id="open_time" name="open_time" value="{{ old('open_time', $openTime) }}"
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label for="close_time" class="block text-sm font-semibold text-slate-700">Jam Tutup</label>
                        <input type="time" id="close_time" name="close_time" value="{{ old('close_time', $closeTime) }}"
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>

                <div>
                    <label for="quota" class="block text-sm font-semibold text-slate-700">Kuota Booking per Hari</label>
                    <input type="number" id="quota" name="quota" value="{{ old('quota', $quota) }}" min="0" step="1"
                           class="mt-1.5 w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <p class="mt-1 text-xs text-slate-500">Booking dibatalkan/ditolak tidak menghabiskan kuota.</p>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="duration" class="block text-sm font-semibold text-slate-700">Durasi (menit)</label>
                        <input type="number" id="duration" name="duration" value="{{ old('duration', $duration) }}" min="1" max="600"
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label for="break_start" class="block text-sm font-semibold text-slate-700">Istirahat Mulai</label>
                        <input type="time" id="break_start" name="break_start" value="{{ old('break_start', $breakStart) }}"
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label for="break_end" class="block text-sm font-semibold text-slate-700">Istirahat Selesai</label>
                        <input type="time" id="break_end" name="break_end" value="{{ old('break_end', $breakEnd) }}"
                               class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>
                <p class="text-xs text-slate-500">Kosongkan jam istirahat bila tidak ada.</p>
            </div>

            <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>

    {{-- Kanan: jadwal pemeriksa per hari --}}
    <div class="space-y-6 xl:col-span-3">
        @php
            $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $monthLabel = $monthNames[((int) substr($month, 5, 2)) - 1].' '.substr($month, 0, 4);
            $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Jadwal Pemeriksa per Hari</h2>
                    <p class="mt-0.5 text-sm text-slate-600">
                        Pola berlaku untuk seluruh bulan <span class="font-semibold text-slate-900">{{ $monthLabel }}</span> —
                        mis. Senin otomatis memakai pemeriksa yang sama untuk semua Senin di bulan itu.
                    </p>
                </div>

                {{-- Pilih bulan + salin --}}
                <form action="{{ route('admin.schedule.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="month" name="month" value="{{ $month }}"
                           class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <button type="submit"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-600">
                        Tampilkan
                    </button>
                </form>
            </div>

            @if ($hasPrevious)
                <form action="{{ route('admin.schedule.weekly-copy') }}" method="POST" class="mt-4"
                      data-confirm="Salin jadwal pemeriksa dari bulan sebelumnya? Penugasan yang sudah ada tidak akan tertimpa."
                      data-confirm-accept="Ya, Salin">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit"
                            class="rounded-lg bg-sky-50 px-4 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">
                        ↔ Salin jadwal dari bulan sebelumnya
                    </button>
                </form>
            @endif

            @if (! $autoAssign)
                <p class="mt-4 rounded-lg bg-amber-50 px-4 py-2.5 text-xs font-medium text-amber-700">
                    Penugasan otomatis belum diaktifkan di Pengaturan Umum — jadwal di bawah hanya berlaku saat toggle dinyalakan.
                </p>
            @endif

            <form action="{{ route('admin.schedule.weekly-store') }}" method="POST" class="mt-5">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">

                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="w-32 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Hari</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pemeriksa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($dayNames as $day => $name)
                                @php
                                    $assigned = $weekly[$day] ?? collect();
                                    $isOpenDay = in_array($day, $days, true);
                                @endphp
                                <tr class="{{ $isOpenDay ? '' : 'bg-slate-50/60' }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-slate-900">{{ $name }}</span>
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $isOpenDay ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                                                {{ $isOpenDay ? 'Operasional' : 'Tutup' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-x-5 gap-y-1.5">
                                            @forelse ($examiners as $examiner)
                                                <label class="flex cursor-pointer items-center gap-1.5 text-sm text-slate-700">
                                                    <input type="checkbox" name="days[{{ $day }}][]" value="{{ $examiner->id }}"
                                                           {{ $assigned->contains('user_id', $examiner->id) ? 'checked' : '' }}
                                                           class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                    {{ $examiner->name }}
                                                </label>
                                            @empty
                                                <p class="text-sm text-slate-400">Belum ada akun laboran. Buat di Kelola User.</p>
                                            @endforelse
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">
                        Centang lebih dari satu pemeriksa bila perlu — booking akan dibagi rata secara acak di antara mereka.
                    </p>
                    <button type="submit"
                            class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                        Simpan Jadwal {{ $monthLabel }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Ringkasan --}}
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
            <p class="text-sm font-bold text-emerald-800">Ringkasan yang tampil ke pengguna</p>
            <p class="mt-2 text-sm leading-relaxed text-emerald-700">
                @if ($enabled)
                    Hari operasional: <span class="font-semibold">{{ implode(', ', array_map(fn ($d) => $dayNames[$d] ?? '', $days)) }}</span> ·
                    Jam: <span class="font-semibold">{{ $openTime }}–{{ $closeTime }}</span> ·
                    Kuota: <span class="font-semibold">{{ $quota }} booking/hari</span> ·
                    Durasi: <span class="font-semibold">±{{ $duration }} menit/orang</span>
                    @if ($breakStart && $breakEnd)
                        · Istirahat: <span class="font-semibold">{{ $breakStart }}–{{ $breakEnd }}</span>
                    @endif
                    · Penugasan otomatis: <span class="font-semibold">{{ $autoAssign ? 'Aktif' : 'Nonaktif' }}</span>
                @else
                    Jadwal layanan nonaktif — semua hari terbuka tanpa batas kuota.
                @endif
            </p>
        </div>
    </div>
</div>

@endsection
