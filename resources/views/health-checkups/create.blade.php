@extends('layouts.app')

@section('title', 'Booking Pemeriksaan Kesehatan - MarketLabs')

@section('content')

<section class="py-16">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('health-checkups.catalog') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
            ← Kembali ke Katalog Pemeriksaan
        </a>

        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Booking Pemeriksaan</h1>
        <p class="mt-1 text-sm text-slate-600">
            Pilih jenis pemeriksaan dan tanggal kedatangan. Nomor antrian akan diberikan otomatis per hari.
        </p>

        @if ($schedule['enabled'])
            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                <p class="font-bold">Jadwal Layanan</p>
                <p class="mt-1">
                    Hari operasional: <span class="font-semibold">{{ $schedule['day_names'] }}</span> ·
                    Jam: <span class="font-semibold">{{ $schedule['open_time'] }}–{{ $schedule['close_time'] }}</span> ·
                    @if ($schedule['break_start'] && $schedule['break_end'])
                        Istirahat: <span class="font-semibold">{{ $schedule['break_start'] }}–{{ $schedule['break_end'] }}</span> ·
                    @endif
                    Kuota: <span class="font-semibold">{{ $schedule['quota'] }} booking/hari</span>
                </p>
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

        <form action="{{ route('health-checkups.store') }}" method="POST" class="mt-8 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            @csrf

            <div>
                <label for="type_id" class="block text-sm font-semibold text-slate-700">Jenis Pemeriksaan <span class="text-red-500">*</span></label>
                <select id="type_id" name="type_id" required
                        class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">— Pilih jenis pemeriksaan —</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}"
                                {{ old('type_id', $selectedType?->id) == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} — {{ $type->formatted_price }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-6">
                <label for="booking_date" class="block text-sm font-semibold text-slate-700">Tanggal Kedatangan <span class="text-red-500">*</span></label>
                <input type="date" id="booking_date" name="booking_date" value="{{ old('booking_date') }}"
                       min="{{ now()->format('Y-m-d') }}" required
                       class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <p id="booking-date-hint" class="mt-1.5 text-xs text-slate-500">Nomor antrian harian (Q-001, Q-002, dst.) diberikan otomatis berdasarkan tanggal ini.</p>

                <div id="estimate-box" class="mt-4 hidden rounded-xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm text-sky-800">
                    <p class="text-xs font-bold uppercase tracking-wider text-sky-500">Posisi Antrian Anda</p>
                    <p id="estimate-text" class="mt-1"></p>
                </div>
            </div>

            <div class="mt-6">
                <label for="purpose" class="block text-sm font-semibold text-slate-700">Tujuan Pemeriksaan</label>
                <input type="text" id="purpose" name="purpose" value="{{ old('purpose') }}" maxlength="500"
                       placeholder="Contoh: Persyaratan melamar kerja / pendidikan"
                       class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="mt-6 rounded-xl bg-slate-50 px-5 py-4 text-sm text-slate-600">
                <p>Data diri Anda (nama, NIM/NIP, instansi) diambil dari <a href="{{ route('profile.show') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">halaman profil</a>. Pastikan sudah lengkap.</p>
            </div>

            <button type="submit"
                    class="mt-8 w-full rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700">
                Buat Booking
            </button>
        </form>
    </div>
</section>

@push('scripts')
<script>
    (function () {
        const dateInput = document.getElementById('booking_date');
        const hint = document.getElementById('booking-date-hint');
        const box = document.getElementById('estimate-box');
        const text = document.getElementById('estimate-text');

        if (!dateInput || !hint || !box || !text) return;

        const openDays = @json($schedule['days']);
        const estimateUrl = @json(route('health-checkups.estimate'));

        function loadEstimate() {
            const date = dateInput.value;
            if (!date) {
                box.classList.add('hidden');
                return;
            }

            fetch(estimateUrl + '?date=' + encodeURIComponent(date), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok && data.queue) {
                        const q = data.queue;
                        box.classList.remove('hidden');
                        let html = 'Nomor antrian: <span class="font-semibold">' + q.queue_label + '</span>';
                        html += ' · Posisi antrian: <span class="font-semibold">ke-' + q.position + ' dari ' + q.waiting + '</span>';
                        if (q.people_ahead > 0) {
                            html += '<br><span class="text-xs">Masih ada ' + q.people_ahead + ' orang di depan Anda.</span>';
                        } else {
                            html += '<br><span class="text-xs">Anda urutan berikutnya.</span>';
                        }
                        if (q.examiner_name) {
                            html += '<br><span class="text-xs">Pemeriksa: ' + q.examiner_name + ' (perkiraan — bisa berubah saat booking)</span>';
                        }
                        html += '<br><span class="text-xs font-bold text-amber-600">⚠ Pantau posisi antrian Anda di halaman detail booking.</span>';
                        text.innerHTML = html;
                    } else {
                        box.classList.add('hidden');
                    }
                })
                .catch(function () { box.classList.add('hidden'); });
        }

        @if ($schedule['enabled'])
        dateInput.addEventListener('change', function () {
            if (!this.value) return;

            // getDay(): 0 = Minggu ... 6 = Sabtu → konversi ke 1 = Senin ... 7 = Minggu.
            const jsDay = new Date(this.value + 'T00:00:00').getDay();
            const isoDay = jsDay === 0 ? 7 : jsDay;

            if (!openDays.includes(isoDay)) {
                hint.textContent = 'Tanggal tersebut bukan hari operasional. Pilih tanggal lain.';
                hint.classList.remove('text-slate-500');
                hint.classList.add('font-semibold', 'text-red-600');
                box.classList.add('hidden');
            } else {
                hint.textContent = 'Nomor antrian harian (Q-001, Q-002, dst.) diberikan otomatis berdasarkan tanggal ini.';
                hint.classList.add('text-slate-500');
                hint.classList.remove('font-semibold', 'text-red-600');
                loadEstimate();
            }
        });
        @else
        dateInput.addEventListener('change', loadEstimate);
        @endif

        // Muat estimasi bila tanggal sudah terisi (mis. setelah error validasi).
        if (dateInput.value) {
            loadEstimate();
        }
    })();
</script>
@endpush

@endsection
