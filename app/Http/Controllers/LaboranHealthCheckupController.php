<?php

namespace App\Http\Controllers;

use App\Models\HealthCheckup;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LaboranHealthCheckupController extends Controller
{
    /**
     * Laboran mengubah status pemeriksaan yang ditugaskan kepadanya.
     */
    public function updateStatus(Request $request, HealthCheckup $checkup)
    {
        abort_unless($checkup->examiner_id === auth()->id(), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                HealthCheckup::STATUS_APPROVED,
                HealthCheckup::STATUS_DONE,
                HealthCheckup::STATUS_REJECTED,
            ])],
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
                "Pemeriksaan {$checkup->code} telah selesai diproses.",
            ],
            HealthCheckup::STATUS_REJECTED => [
                'Booking Pemeriksaan Ditolak',
                "Booking {$checkup->code} ditolak.",
            ],
            default => [$checkup->code, null],
        };

        $checkup->user->notify(new BorrowingNotification($title, $message, route('health-checkups.show', $checkup), notifyViaEmail: true));

        // Beri tahu admin bahwa status booking diubah oleh laboran.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                'Status Booking Diubah',
                "Booking {$checkup->code} ditandai '".HealthCheckup::statusLabel($validated['status'])."' oleh ".auth()->user()->name.'.',
                route('admin.health-checkups.show', $checkup),
                notifyViaEmail: true,
            ));
        }

        return back()->with('success', 'Status booking '.$checkup->code.' diperbarui menjadi '.HealthCheckup::statusLabel($validated['status']).'.');
    }

    /**
     * Laboran mengubah status pembayaran pemeriksaan yang ditugaskan kepadanya.
     */
    public function updatePayment(Request $request, HealthCheckup $checkup)
    {
        abort_unless($checkup->examiner_id === auth()->id(), 403);

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

        // Beri tahu admin bahwa pembayaran booking diubah oleh laboran.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                $paid ? 'Pembayaran Booking Lunas' : 'Pembayaran Booking Belum Dibayar',
                "Pembayaran {$checkup->code} ditandai ".($paid ? 'lunas' : 'belum dibayar').' oleh '.auth()->user()->name.'.',
                route('admin.health-checkups.show', $checkup),
                notifyViaEmail: true,
            ));
        }

        return back()->with('success', 'Status pembayaran '.$checkup->code.' diperbarui menjadi '.HealthCheckup::paymentStatusLabel($validated['payment_status']).'.');
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-MCU-'.date('Ymd');

        do {
            $number = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (HealthCheckup::where('invoice_number', $number)->exists());

        return $number;
    }

    /**
     * Laboran menginput hasil pemeriksaan.
     */
    public function updateResult(Request $request, HealthCheckup $checkup)
    {
        abort_unless($checkup->examiner_id === auth()->id(), 403);

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
            $userName = Str::slug($checkup->user->name);
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

        // Beri tahu admin bahwa hasil pemeriksaan diinput oleh laboran.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                'Hasil Pemeriksaan Diinput',
                "Hasil pemeriksaan {$checkup->code} diinput oleh ".auth()->user()->name.'.',
                route('admin.health-checkups.show', $checkup),
                notifyViaEmail: true,
            ));
        }

        return back()->with('success', "Hasil pemeriksaan {$checkup->code} berhasil disimpan.");
    }
}
