<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratorium;
use App\Models\ResearchProposal;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use App\Support\ExcelExport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminResearchProposalController extends Controller
{
    protected function applyFilters(Request $request, $query)
    {
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('code', 'like', "%{$escaped}%")
                    ->orWhere('title', 'like', "%{$escaped}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$escaped}%"));
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->applyFilters($request, ResearchProposal::with('user')->latest());

        $proposals = $query->paginate(15)->withQueryString();

        return view('admin.research.index', compact('proposals'));
    }

    public function export(Request $request): StreamedResponse
    {
        $proposals = $this->applyFilters($request, ResearchProposal::with(['user', 'laboran', 'laboratorium', 'tools'])->latest())->get();

        $rows = [[
            'Kode', 'Judul', 'Bidang', 'Pemohon', 'Email', 'NIM/NIP/NIDN/NIK', 'Instansi',
            'Tanggal Mulai', 'Tanggal Selesai', 'Lama Hari',
            'Bench Fee (Rp)', 'Biaya Laboran (Rp)', 'Denda (Rp)', 'Total Sewa Alat (Rp)', 'Total Keseluruhan (Rp)',
            'Status', 'Status Pembayaran', 'No. Invoice', 'Membutuhkan Laboran', 'Laboran', 'Laboratorium',
            'Diajukan Pada',
        ]];

        foreach ($proposals as $proposal) {
            $rows[] = [
                $proposal->code,
                $proposal->title,
                $proposal->field ?? '-',
                $proposal->user->name,
                $proposal->user->email,
                $proposal->nim_nip ?? '-',
                $proposal->institution ?? '-',
                $proposal->start_date?->format('d/m/Y') ?? '-',
                $proposal->end_date?->format('d/m/Y') ?? '-',
                $proposal->duration_days,
                $proposal->bench_fee ?? 0,
                $proposal->laboran_fee ?? 0,
                $proposal->penalty ?? 0,
                $proposal->tools_subtotal,
                $proposal->grand_total,
                ResearchProposal::statusLabel($proposal->status),
                ResearchProposal::paymentStatusLabel($proposal->payment_status),
                $proposal->invoice_number ?? '-',
                $proposal->needs_laboran ? 'Ya' : 'Tidak',
                $proposal->laboran?->name ?? '-',
                $proposal->laboratorium?->name ?? '-',
                $proposal->created_at->format('d/m/Y H:i'),
            ];
        }

        return ExcelExport::download('riset-' . now()->format('Ymd-His') . '.xlsx', $rows);
    }

    public function show(ResearchProposal $proposal)
    {
        $proposal->load(['user', 'members', 'tools.category', 'laboran', 'laboratorium', 'logbooks']);

        $laborans = User::laboran()->orderBy('name')->get();
        $laboratoriums = Laboratorium::active()->orderBy('name')->get();

        return view('admin.research.show', compact('proposal', 'laborans', 'laboratoriums'));
    }

    public function updateAssignment(Request $request, ResearchProposal $proposal)
    {
        $validated = $request->validate([
            'laboran_id' => ['nullable', 'string', Rule::exists('users', 'id')->where('role', User::ROLE_LABORAN)],
            'laboran_fee' => ['nullable', 'integer', 'min:0'],
            'laboratorium_id' => ['nullable', 'string', 'exists:laboratoriums,id'],
        ]);

        $proposal->update([
            'laboran_id' => $validated['laboran_id'] ?? null,
            'laboran_fee' => $validated['laboran_fee'] ?? null,
            'laboratorium_id' => $validated['laboratorium_id'] ?? null,
        ]);

        // Beri tahu laboran bila riset ditugaskan kepadanya.
        if (! empty($validated['laboran_id'])) {
            User::find($validated['laboran_id'])?->notify(new BorrowingNotification(
                'Riset Ditugaskan',
                "Permohonan riset {$proposal->code} \"{$proposal->title}\" ditugaskan kepada Anda oleh admin.",
                route('research.show', $proposal),
                notifyViaEmail: true,
            ));
        }

        return back()->with('success', "Penugasan laboran & laboratorium untuk {$proposal->code} berhasil disimpan.");
    }

    public function updateStatus(Request $request, ResearchProposal $proposal)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                ResearchProposal::STATUS_APPROVED,
                ResearchProposal::STATUS_ONGOING,
                ResearchProposal::STATUS_REJECTED,
                ResearchProposal::STATUS_DONE,
            ])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $timestamp = match ($validated['status']) {
            ResearchProposal::STATUS_APPROVED => 'approved_at',
            ResearchProposal::STATUS_ONGOING => 'ongoing_at',
            ResearchProposal::STATUS_REJECTED => 'rejected_at',
            ResearchProposal::STATUS_DONE => 'done_at',
            default => null,
        };

        $proposal->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'processed_at' => now(),
            $timestamp => now(),
        ]);

        $url = route('research.show', $proposal);
        $code = $proposal->code;

        [$title, $message] = match ($validated['status']) {
            ResearchProposal::STATUS_APPROVED => [
                'Permohonan Riset Disetujui',
                "Permohonan riset {$code} telah disetujui. Silakan cek detail untuk informasi selanjutnya.",
            ],
            ResearchProposal::STATUS_ONGOING => [
                'Permohonan Riset Sedang Berlangsung',
                "Permohonan riset {$code} sedang berlangsung.",
            ],
            ResearchProposal::STATUS_REJECTED => [
                'Permohonan Riset Ditolak',
                "Permohonan riset {$code} ditolak. Cek catatan admin pada detail permohonan.",
            ],
            ResearchProposal::STATUS_DONE => [
                'Permohonan Riset Selesai',
                "Permohonan riset {$code} telah ditandai selesai.",
            ],
            default => [$code, null],
        };

        $proposal->user->notify(new BorrowingNotification($title, $message, $url, notifyViaEmail: true));

        return back()->with('success', 'Status permohonan riset ' . $code . ' diperbarui menjadi ' . ResearchProposal::statusLabel($validated['status']) . '.');
    }

    public function logbook(ResearchProposal $proposal)
    {
        $proposal->load(['user', 'logbooks']);

        return view('admin.research.logbook', compact('proposal'));
    }

    public function logbookPrint(ResearchProposal $proposal)
    {
        $proposal->load(['user', 'logbooks', 'laboran']);

        $backUrl = route('admin.research.logbook', $proposal);

        return view('research.logbook-print', compact('proposal', 'backUrl'));
    }

    public function updatePenalty(Request $request, ResearchProposal $proposal)
    {
        $validated = $request->validate([
            'penalty' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'penalty_note' => ['nullable', 'string', 'max:500'],
        ]);

        $proposal->update([
            'penalty' => $validated['penalty'],
            'penalty_note' => trim((string) ($validated['penalty_note'] ?? '')) !== '' ? trim($validated['penalty_note']) : null,
        ]);

        if ($validated['penalty'] > 0) {
            $proposal->user->notify(new BorrowingNotification(
                'Denda Biaya Tambahan',
                "Permohonan riset {$proposal->code} dikenakan biaya tambahan (denda) sebesar Rp " . number_format($validated['penalty'], 0, ',', '.') . ". Silakan cek detail permohonan.",
                route('research.show', $proposal),
                notifyViaEmail: true,
            ));
        }

        return back()->with('success', 'Denda / biaya tambahan ' . $proposal->code . ' diperbarui.');
    }

    public function updatePayment(Request $request, ResearchProposal $proposal)
    {
        $validated = $request->validate([
            'payment_status' => ['required', Rule::in([ResearchProposal::PAYMENT_UNPAID, ResearchProposal::PAYMENT_PAID])],
        ]);

        $data = [
            'payment_status' => $validated['payment_status'],
        ];

        // Terbitkan nomor invoice saat pertama kali ditandai lunas.
        if ($validated['payment_status'] === ResearchProposal::PAYMENT_PAID && ! $proposal->invoice_number) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        $proposal->update($data);

        $paid = $validated['payment_status'] === ResearchProposal::PAYMENT_PAID;

        $proposal->user->notify(new BorrowingNotification(
            $paid ? 'Pembayaran Lunas' : 'Pembayaran Belum Dibayar',
            $paid
                ? "Pembayaran permohonan riset {$proposal->code} telah ditandai lunas. Silakan cek invoice pada detail permohonan."
                : "Status pembayaran permohonan riset {$proposal->code} diubah menjadi belum dibayar.",
            route('research.show', $proposal),
            notifyViaEmail: true,
        ));

        return back()->with('success', 'Status pembayaran ' . $proposal->code . ' diperbarui menjadi ' . ResearchProposal::paymentStatusLabel($validated['payment_status']) . '.');
    }

    public function invoice(ResearchProposal $proposal)
    {
        $proposal->load(['user', 'members', 'tools.category', 'laboran', 'laboratorium']);

        return view('research.invoice', compact('proposal'));
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-RST-' . date('Ymd');

        do {
            $number = $prefix . '-' . strtoupper(substr(uniqid(), -5));
        } while (ResearchProposal::where('invoice_number', $number)->exists());

        return $number;
    }
}
