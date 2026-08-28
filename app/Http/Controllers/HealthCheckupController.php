<?php

namespace App\Http\Controllers;

use App\Models\HealthCheckup;
use App\Models\HealthTestType;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use App\Support\ServiceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HealthCheckupController extends Controller
{
    /**
     * Katalog publik jenis pemeriksaan kesehatan.
     */
    public function catalog()
    {
        $types = HealthTestType::active()->orderBy('name')->get();
        $schedule = ServiceSchedule::summary();

        return view('health-checkups.catalog', compact('types', 'schedule'));
    }

    /**
     * Riwayat booking pemeriksaan milik user.
     */
    public function index()
    {
        $checkups = HealthCheckup::with('type')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Posisi antrian per tanggal untuk daftar riwayat (per halaman).
        $queues = [];
        foreach ($checkups->groupBy(fn ($c) => $c->booking_date->toDateString()) as $date => $items) {
            $queues[$date] = ServiceSchedule::queueInfo($date);
        }

        return view('health-checkups.index', compact('checkups', 'queues'));
    }

    public function create(Request $request)
    {
        $types = HealthTestType::active()->orderBy('name')->get();
        $schedule = ServiceSchedule::summary();

        $selectedType = null;

        if ($request->query('type')) {
            $selectedType = HealthTestType::active()->where('key', $request->query('type'))->first();
        }

        return view('health-checkups.create', compact('types', 'selectedType', 'schedule'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_id' => ['required', 'string', 'exists:health_test_types,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'purpose' => ['nullable', 'string', 'max:500'],
        ]);

        $type = HealthTestType::active()->findOrFail($validated['type_id']);

        // Validasi terhadap jadwal layanan (hari operasional & kuota per hari).
        if (ServiceSchedule::enabled()) {
            if (! ServiceSchedule::isOpenOn($validated['booking_date'])) {
                return back()->withErrors(['booking_date' => 'Tanggal tersebut bukan hari operasional.'])->withInput();
            }
        }

        $checkup = DB::transaction(function () use ($validated, $type) {
            // Kunci baris booking pada tanggal tersebut agar perhitungan nomor
            // antrian dan kuota aman dari race condition (dua booking bersamaan).
            HealthCheckup::whereDate('booking_date', $validated['booking_date'])
                ->orderBy('queue_number')
                ->lockForUpdate()
                ->get();

            if (ServiceSchedule::enabled() && ! ServiceSchedule::hasQuota($validated['booking_date'])) {
                throw ValidationException::withMessages([
                    'booking_date' => 'Kuota booking untuk tanggal tersebut sudah penuh (maksimal '.ServiceSchedule::quota().' per hari).',
                ]);
            }

            // Nomor antrian harian: lanjut dari nomor terbesar di tanggal tersebut.
            $queue = (int) HealthCheckup::whereDate('booking_date', $validated['booking_date'])->max('queue_number') + 1;

            return HealthCheckup::create([
                'user_id' => auth()->id(),
                'type_id' => $type->id,
                'code' => $this->generateCode(),
                'booking_date' => $validated['booking_date'],
                'queue_number' => $queue,
                'purpose' => trim((string) ($validated['purpose'] ?? '')) !== '' ? trim($validated['purpose']) : null,
                'status' => HealthCheckup::STATUS_PENDING,
                'payment_status' => HealthCheckup::PAYMENT_UNPAID,
            ]);
        });

        // Penugasan otomatis pemeriksa (bila diaktifkan admin).
        $examinerId = ServiceSchedule::assignExaminer($checkup);

        // Beri tahu admin bahwa ada booking baru.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                'Booking Pemeriksaan Baru',
                "Booking {$checkup->code} ({$checkup->queue_label}) dibuat oleh ".auth()->user()->name.' untuk '.$checkup->booking_date->translatedFormat('d M Y').'.',
                route('admin.health-checkups.show', $checkup),
                notifyViaEmail: true,
            ));
        }

        // Beri tahu pemeriksa (laboran) bila otomatis ditugaskan.
        if ($examinerId) {
            User::find($examinerId)?->notify(new BorrowingNotification(
                'Booking Pemeriksaan Ditugaskan',
                "Booking {$checkup->code} (nomor antrian {$checkup->queue_label}) pada ".$checkup->booking_date->translatedFormat('d M Y').' ditugaskan kepada Anda.',
                route('laboran.index'),
                notifyViaEmail: true,
            ));
        }

        return redirect()->route('health-checkups.show', $checkup)
            ->with('success', "Booking {$checkup->code} berhasil dibuat. Nomor antrian Anda: {$checkup->queue_label}.");
    }

    /**
     * Estimasi live slot untuk booking berikutnya pada tanggal terpilih (JSON),
     * dipakai pratinjau di form booking.
     */
    public function estimate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => $validator->errors()->first()], 422);
        }

        return response()->json([
            'ok' => true,
            'queue' => ServiceSchedule::nextQueue($validator->validated()['date']),
        ]);
    }

    public function show(HealthCheckup $checkup)
    {
        abort_unless($checkup->user_id === auth()->id(), 403);

        $checkup->load(['type', 'examiner']);

        $queue = ServiceSchedule::queueInfo($checkup->booking_date)[$checkup->id] ?? null;

        return view('health-checkups.show', compact('checkup', 'queue'));
    }

    /**
     * Tiket nomor antrian yang bisa dicetak.
     */
    public function ticket(HealthCheckup $checkup)
    {
        abort_unless($checkup->user_id === auth()->id(), 403);

        $checkup->load(['type', 'examiner']);

        $queue = ServiceSchedule::queueInfo($checkup->booking_date)[$checkup->id] ?? null;
        $schedule = ServiceSchedule::summary();

        return view('health-checkups.ticket', compact('checkup', 'queue', 'schedule'));
    }

    public function invoice(HealthCheckup $checkup)
    {
        abort_unless($checkup->user_id === auth()->id(), 403);
        abort_unless($checkup->invoice_number, 404);

        $checkup->load('type');

        return view('health-checkups.invoice', compact('checkup'));
    }

    public function certificate(HealthCheckup $checkup)
    {
        abort_unless($checkup->user_id === auth()->id(), 403);
        abort_unless($checkup->status === HealthCheckup::STATUS_DONE, 404);

        $checkup->load('type');

        return view('health-checkups.certificate', compact('checkup'));
    }

    public function cancel(HealthCheckup $checkup)
    {
        abort_unless($checkup->user_id === auth()->id(), 403);
        abort_unless(in_array($checkup->status, [HealthCheckup::STATUS_PENDING, HealthCheckup::STATUS_APPROVED]), 403);

        $checkup->update(['status' => HealthCheckup::STATUS_CANCELLED]);

        return redirect()->route('health-checkups.index')
            ->with('success', "Booking {$checkup->code} berhasil dibatalkan.");
    }

    protected function generateCode(): string
    {
        $prefix = 'MCU-'.date('Ymd');

        do {
            $code = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (HealthCheckup::where('code', $code)->exists());

        return $code;
    }
}
