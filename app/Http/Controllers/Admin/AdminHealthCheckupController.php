<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HealthCheckup;
use App\Models\HealthTestType;
use App\Notifications\BorrowingNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminHealthCheckupController extends Controller
{
    public function index(Request $request)
    {
        $query = HealthCheckup::with(['user', 'type']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('type_id', $type);
        }

        if ($date = $request->query('date')) {
            $query->whereDate('booking_date', $date);
        }

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('code', 'like', "%{$escaped}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$escaped}%"));
            });
        }

        $checkups = $query->latest()->paginate(15)->withQueryString();
        $types = HealthTestType::orderBy('name')->get();

        return view('admin.health-checkups.index', compact('checkups', 'types'));
    }

    public function show(HealthCheckup $checkup)
    {
        $checkup->load(['user', 'type', 'examiner']);

        return view('admin.health-checkups.show', compact('checkup'));
    }

    public function updateStatus(Request $request, HealthCheckup $checkup)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                HealthCheckup::STATUS_APPROVED,
                HealthCheckup::STATUS_DONE,
                HealthCheckup::STATUS_REJECTED,
                HealthCheckup::STATUS_CANCELLED,
            ])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $timestamp = match ($validated['status']) {
            HealthCheckup::STATUS_APPROVED => 'approved_at',
            HealthCheckup::STATUS_DONE => 'done_at',
            HealthCheckup::STATUS_REJECTED => 'rejected_at',
            default => null,
        };

        $data = [
            'status' => $validated['status'],
            'processed_at' => now(),
        ];

        if ($timestamp) {
            $data[$timestamp] = now();
        }

        $checkup->update($data);

        [$title, $message] = match ($validated['status']) {
            HealthCheckup::STATUS_APPROVED => [
                'Booking Pemeriksaan Dikonfirmasi',
                "Booking {$checkup->code} telah dikonfirmasi. Datang pada {$checkup->booking_date->translatedFormat('d M Y')} dengan nomor antrian {$checkup->queue_label}.",
            ],
            HealthCheckup::STATUS_DONE => [
                'Pemeriksaan Selesai',
                "Pemeriksaan {$checkup->code} telah selesai. Hasil tersedia di detail booking.",
            ],
            HealthCheckup::STATUS_REJECTED => [
                'Booking Pemeriksaan Ditolak',
                "Booking {$checkup->code} ditolak.",
            ],
            HealthCheckup::STATUS_CANCELLED => [
                'Booking Pemeriksaan Dibatalkan',
                "Booking {$checkup->code} dibatalkan.",
            ],
            default => [$checkup->code, null],
        };

        $checkup->user->notify(new BorrowingNotification($title, $message, route('health-checkups.show', $checkup), notifyViaEmail: true));

        return back()->with('success', 'Status booking '.$checkup->code.' diperbarui menjadi '.HealthCheckup::statusLabel($validated['status']).'.');
    }

    public function updateResult(Request $request, HealthCheckup $checkup)
    {
        $validated = $request->validate([
            'result' => ['required', 'string', 'max:255'],
            'result_notes' => ['nullable', 'string', 'max:2000'],
            'result_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $resultFile = $checkup->result_file;

        if ($request->hasFile('result_file')) {
            if ($resultFile && \Storage::disk('local')->exists($resultFile)) {
                \Storage::disk('local')->delete($resultFile);
            } elseif ($resultFile && \Storage::disk('public')->exists($resultFile)) {
                \Storage::disk('public')->delete($resultFile);
            }
            $userName = \Illuminate\Support\Str::slug($checkup->user->name);
            $ext = strtolower($request->file('result_file')->getClientOriginalExtension());
            $resultFile = $request->file('result_file')->storeAs('health-checkup-results', 'hasil-'.$checkup->code.'-'.$userName.'.'.$ext);
        }

        $checkup->update([
            'result' => $validated['result'],
            'result_notes' => trim((string) ($validated['result_notes'] ?? '')) !== '' ? trim($validated['result_notes']) : null,
            'result_file' => $resultFile,
        ]);

        $checkup->user->notify(new BorrowingNotification(
            'Hasil Pemeriksaan Tersedia',
            "Hasil pemeriksaan {$checkup->code} telah diinput: {$validated['result']}. Silakan cek detail booking.",
            route('health-checkups.show', $checkup),
            notifyViaEmail: true,
        ));

        return back()->with('success', 'Hasil pemeriksaan '.$checkup->code.' berhasil disimpan.');
    }

    public function updatePayment(Request $request, HealthCheckup $checkup)
    {
        $validated = $request->validate([
            'payment_status' => ['required', Rule::in([HealthCheckup::PAYMENT_UNPAID, HealthCheckup::PAYMENT_PAID])],
        ]);

        $data = ['payment_status' => $validated['payment_status']];

        if ($validated['payment_status'] === HealthCheckup::PAYMENT_PAID && ! $checkup->invoice_number) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        $checkup->update($data);

        $paid = $validated['payment_status'] === HealthCheckup::PAYMENT_PAID;

        $checkup->user->notify(new BorrowingNotification(
            $paid ? 'Pembayaran Lunas' : 'Pembayaran Belum Dibayar',
            $paid
                ? "Pembayaran {$checkup->code} telah ditandai lunas. Invoice tersedia di detail booking."
                : "Status pembayaran {$checkup->code} diubah menjadi belum dibayar.",
            route('health-checkups.show', $checkup),
            notifyViaEmail: true,
        ));

        return back()->with('success', 'Status pembayaran '.$checkup->code.' diperbarui menjadi '.HealthCheckup::paymentStatusLabel($validated['payment_status']).'.');
    }

    public function invoice(HealthCheckup $checkup)
    {
        $checkup->load('type');

        return view('health-checkups.invoice', compact('checkup'));
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-MCU-'.date('Ymd');

        do {
            $number = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (HealthCheckup::where('invoice_number', $number)->exists());

        return $number;
    }
}
