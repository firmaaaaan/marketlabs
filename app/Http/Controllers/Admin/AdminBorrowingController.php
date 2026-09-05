<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Tool;
use App\Notifications\BorrowingNotification;
use App\Support\ExcelExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminBorrowingController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with(['user', 'items.tool'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('code', 'like', "%{$escaped}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$escaped}%"));
            });
        }

        $borrowings = $query->paginate(15)->withQueryString();

        return view('admin.borrowings.index', compact('borrowings'));
    }

    public function show(Borrowing $borrowing)
    {
        $borrowing->load(['items.tool', 'user']);

        return view('admin.borrowings.show', compact('borrowing'));
    }

    public function export(Request $request)
    {
        $query = Borrowing::with(['user', 'items.tool'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('code', 'like', "%{$escaped}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$escaped}%"));
            });
        }

        $borrowings = $query->get();

        $rows = [[
            'Kode', 'Nomor Invoice', 'Peminjam', 'Email', 'Jenis Peminjaman',
            'NIM/NIP/NIDN/NIK', 'Instansi', 'Tujuan',
            'Tanggal Pinjam', 'Tanggal Kembali', 'Lama Hari', 'Total Unit',
            'Subtotal (Rp)', 'Diskon (%)', 'Nilai Diskon (Rp)', 'Denda (Rp)', 'Total Biaya (Rp)',
            'Status', 'Status Pembayaran', 'Catatan Peminjam', 'Catatan Pengambilan', 'Dibuat Pada',
        ]];

        foreach ($borrowings as $b) {
            $rows[] = [
                $b->code,
                $b->invoice_number ?? '-',
                $b->user->name,
                $b->user->email,
                $b->borrower_type_label,
                $b->nim_nip ?? '-',
                $b->institution ?? '-',
                $b->purpose ?? '-',
                $b->borrow_date->format('d/m/Y'),
                $b->return_date->format('d/m/Y'),
                $b->duration_days,
                $b->items->sum('quantity'),
                $b->base_cost,
                $b->discount,
                $b->discount_amount,
                $b->penalty,
                $b->total_cost,
                Borrowing::statusLabel($b->status),
                Borrowing::paymentStatusLabel($b->payment_status),
                $b->notes ?? '',
                $b->pickup_notes ?? '',
                $b->created_at->format('d/m/Y H:i'),
            ];
        }

        return ExcelExport::download('peminjaman-'.now()->format('Ymd-His').'.xlsx', $rows);
    }

    public function updateStatus(Request $request, Borrowing $borrowing)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Borrowing::STATUS_APPROVED,
                Borrowing::STATUS_REJECTED,
                Borrowing::STATUS_BORROWED,
                Borrowing::STATUS_RETURNED,
            ])],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:2000'],
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $borrowing->status;

        // Validasi transisi status.
        $allowed = [
            Borrowing::STATUS_PENDING => [Borrowing::STATUS_APPROVED, Borrowing::STATUS_REJECTED],
            Borrowing::STATUS_APPROVED => [Borrowing::STATUS_BORROWED, Borrowing::STATUS_REJECTED],
            Borrowing::STATUS_BORROWED => [Borrowing::STATUS_RETURNED],
        ];

        if (! isset($allowed[$oldStatus]) || ! in_array($newStatus, $allowed[$oldStatus])) {
            return back()->with('error', "Transisi status tidak valid: {$oldStatus} → {$newStatus}.");
        }

        $borrowing->load('items.tool');

        // Kelola stok saat disetujui (kurangi) dan dikembalikan/ditolak (kembalikan).
        if ($newStatus === Borrowing::STATUS_APPROVED && $oldStatus === Borrowing::STATUS_PENDING) {
            $stockError = DB::transaction(function () use ($borrowing) {
                foreach ($borrowing->items as $item) {
                    $tool = Tool::lockForUpdate()->find($item->tool_id);

                    if ($item->quantity > $tool->available_stock) {
                        return $tool->name;
                    }
                }

                foreach ($borrowing->items as $item) {
                    $item->tool->decrement('available_stock', $item->quantity);
                }

                return null;
            });

            if ($stockError) {
                return back()->with('error', "Stok {$stockError} tidak mencukupi untuk disetujui.");
            }
        }

        if (($newStatus === Borrowing::STATUS_RETURNED && $oldStatus === Borrowing::STATUS_BORROWED)
            || ($newStatus === Borrowing::STATUS_REJECTED && $oldStatus === Borrowing::STATUS_APPROVED)) {
            foreach ($borrowing->items as $item) {
                $item->tool->increment('available_stock', $item->quantity);
            }
        }

        $updateData = [
            'status' => $newStatus,
            'processed_at' => now(),
            'returned_at' => $newStatus === Borrowing::STATUS_RETURNED ? now() : null,
        ];

        if ($newStatus === Borrowing::STATUS_REJECTED) {
            $updateData['rejection_reason'] = $validated['rejection_reason'] ?? null;
        }

        $borrowing->update($updateData);

        $this->notifyStatusChange($borrowing, $newStatus);

        return back()->with('success', 'Status peminjaman diperbarui menjadi '.Borrowing::statusLabel($newStatus).'.');
    }

    /**
     * Kirim notifikasi ke peminjam saat status berubah.
     */
    protected function notifyStatusChange(Borrowing $borrowing, string $status): void
    {
        $url = route('borrowings.show', $borrowing);
        $code = $borrowing->code;
        $toolNames = $borrowing->items->pluck('tool.name')->implode(', ');
        $borrowDate = $borrowing->borrow_date->translatedFormat('d M Y');
        $returnDate = $borrowing->return_date->translatedFormat('d M Y');
        $duration = $borrowing->duration_days;

        [$title, $message] = match ($status) {
            Borrowing::STATUS_APPROVED => [
                'Peminjaman Disetujui',
                "Peminjaman {$code} untuk alat <strong>{$toolNames}</strong> telah disetujui.<br><br>".
                "Tanggal pinjam: {$borrowDate}<br>".
                "Tanggal kembali: {$returnDate} ({$duration} hari)<br><br>".
                '<strong>Rincian Biaya:</strong><br>'.
                "• Biaya Sewa: {$borrowing->formatted_base_cost}<br>".
                ($borrowing->discount > 0 ? "• Diskon ({$borrowing->discount}%): -{$borrowing->formatted_discount_amount}<br>" : '').
                "• <strong>Total Biaya: {$borrowing->formatted_total_cost}</strong><br><br>".
                'Silakan mengambil alat sesuai jadwal. Harap mengembalikan tepat waktu agar tidak dikenakan denda keterlambatan.',
            ],
            Borrowing::STATUS_REJECTED => [
                'Peminjaman Ditolak',
                "Peminjaman {$code} untuk alat <strong>{$toolNames}</strong> ditolak oleh admin.".
                ($borrowing->rejection_reason ? "<br><br><strong>Alasan Penolakan:</strong><br>{$borrowing->rejection_reason}" : '').
                '<br><br>Hubungi admin untuk keterangan lebih lanjut.',
            ],
            Borrowing::STATUS_BORROWED => [
                'Alat Sedang Dipinjam',
                "Alat <strong>{$toolNames}</strong> pada peminjaman {$code} telah diserahkan dan sedang dalam masa peminjaman.<br><br>".
                "Tanggal kembali: <strong>{$returnDate}</strong><br><br>".
                'Harap mengembalikan tepat waktu agar tidak dikenakan denda keterlambatan pengembalian.',
            ],
            Borrowing::STATUS_RETURNED => [
                'Peminjaman Selesai',
                "Alat <strong>{$toolNames}</strong> pada peminjaman {$code} telah berhasil dikembalikan.<br><br>".
                'Terima kasih telah menggunakan layanan MarketLabs. Kami menunggu kunjungan Anda berikutnya.',
            ],
            default => [$code, null],
        };

        $borrowing->user->notify(new BorrowingNotification($title, $message, $url, notifyViaEmail: true));
    }

    protected function notifyBorrower(Borrowing $borrowing, string $title, string $message, ?string $url = null): void
    {
        $borrowing->user->notify(new BorrowingNotification(
            $title,
            $message,
            $url ?? route('borrowings.show', $borrowing),
            notifyViaEmail: true,
        ));
    }

    public function updateBilling(Request $request, Borrowing $borrowing)
    {
        $validated = $request->validate([
            'discount' => ['required', 'integer', 'min:0', 'max:100'],
            'penalty' => ['required', 'integer', 'min:0'],
            'pickup_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $borrowing->update([
            'discount' => $validated['discount'],
            'penalty' => $validated['penalty'],
            'pickup_notes' => $validated['pickup_notes'] ?? null,
        ]);

        return back()->with('success', 'Biaya & catatan pengambilan peminjaman '.$borrowing->code.' berhasil diperbarui.');
    }

    public function invoice(Borrowing $borrowing)
    {
        $borrowing->load(['items.tool', 'user']);

        return view('borrowings.invoice', compact('borrowing'));
    }

    public function notificationsAll()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function notifications()
    {
        $user = auth()->user();

        $items = $user->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notifikasi',
                'message' => $n->data['message'] ?? null,
                'url' => $n->data['url'] ?? null,
                'time' => $n->created_at->diffForHumans(),
                'is_read' => $n->read_at !== null,
            ]);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Borrowing::STATUS_APPROVED,
                Borrowing::STATUS_REJECTED,
                Borrowing::STATUS_BORROWED,
                Borrowing::STATUS_RETURNED,
            ])],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'exists:borrowings,id'],
        ]);

        $newStatus = $validated['status'];
        $updated = 0;
        $skipped = 0;

        $allowed = [
            Borrowing::STATUS_PENDING => [Borrowing::STATUS_APPROVED, Borrowing::STATUS_REJECTED],
            Borrowing::STATUS_APPROVED => [Borrowing::STATUS_BORROWED, Borrowing::STATUS_REJECTED],
            Borrowing::STATUS_BORROWED => [Borrowing::STATUS_RETURNED],
        ];

        $borrowings = Borrowing::with('items.tool')->whereIn('id', $validated['ids'])->get();

        foreach ($borrowings as $borrowing) {
            $oldStatus = $borrowing->status;

            if (! isset($allowed[$oldStatus]) || ! in_array($newStatus, $allowed[$oldStatus])) {
                $skipped++;

                continue;
            }

            if ($newStatus === Borrowing::STATUS_APPROVED && $oldStatus === Borrowing::STATUS_PENDING) {
                $stockError = false;
                foreach ($borrowing->items as $item) {
                    if ($item->quantity > $item->tool->available_stock) {
                        $stockError = true;
                        break;
                    }
                }
                if ($stockError) {
                    $skipped++;

                    continue;
                }
                foreach ($borrowing->items as $item) {
                    $item->tool->decrement('available_stock', $item->quantity);
                }
            }

            if (($newStatus === Borrowing::STATUS_RETURNED && $oldStatus === Borrowing::STATUS_BORROWED)
                || ($newStatus === Borrowing::STATUS_REJECTED && $oldStatus === Borrowing::STATUS_APPROVED)) {
                foreach ($borrowing->items as $item) {
                    $item->tool->increment('available_stock', $item->quantity);
                }
            }

            $updateData = [
                'status' => $newStatus,
                'processed_at' => now(),
                'returned_at' => $newStatus === Borrowing::STATUS_RETURNED ? now() : null,
            ];

            if ($newStatus === Borrowing::STATUS_REJECTED) {
                $updateData['rejection_reason'] = $validated['rejection_reason'] ?? null;
            }

            $borrowing->update($updateData);
            $this->notifyStatusChange($borrowing, $newStatus);
            $updated++;
        }

        $message = "{$updated} peminjaman berhasil diperbarui.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati (transisi tidak valid atau stok tidak cukup).";
        }

        return back()->with('success', $message);
    }

    public function updatePayment(Request $request, Borrowing $borrowing)
    {
        $validated = $request->validate([
            'payment_status' => ['required', Rule::in([Borrowing::PAYMENT_UNPAID, Borrowing::PAYMENT_PAID])],
        ]);

        $borrowing->update([
            'payment_status' => $validated['payment_status'],
        ]);

        $paid = $validated['payment_status'] === Borrowing::PAYMENT_PAID;

        $this->notifyBorrower(
            $borrowing,
            $paid ? 'Pembayaran Lunas' : 'Pembayaran Belum Dibayar',
            $paid
                ? "Pembayaran peminjaman {$borrowing->code} telah ditandai lunas. Terima kasih!"
                : "Status pembayaran peminjaman {$borrowing->code} diubah menjadi belum dibayar.",
        );

        return back()->with('success', 'Status pembayaran '.$borrowing->code.' diperbarui menjadi '.Borrowing::paymentStatusLabel($validated['payment_status']).'.');
    }
}
