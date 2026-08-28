<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Tool;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use App\Support\ExcelExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BorrowingController extends Controller
{
    public function index()
    {
        $borrowings = Borrowing::with(['items.tool'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('borrowings.index', compact('borrowings'));
    }

    public function export(): StreamedResponse
    {
        $borrowings = Borrowing::with(['items.tool'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $rows = [[
            'Kode', 'Nomor Invoice', 'Jenis Peminjaman',
            'Tanggal Pinjam', 'Tanggal Kembali', 'Lama Hari', 'Total Unit',
            'Subtotal (Rp)', 'Diskon (%)', 'Nilai Diskon (Rp)', 'Denda (Rp)', 'Total Biaya (Rp)',
            'Status', 'Status Pembayaran', 'Tujuan', 'Catatan Pengambilan', 'Dibuat Pada',
        ]];

        foreach ($borrowings as $b) {
            $rows[] = [
                $b->code,
                $b->invoice_number ?? '-',
                $b->borrower_type_label,
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
                $b->purpose ?? '-',
                $b->pickup_notes ?? '',
                $b->created_at->format('d/m/Y H:i'),
            ];
        }

        return ExcelExport::download('riwayat-peminjaman-'.now()->format('Ymd-His').'.xlsx', $rows);
    }

    public function show(Borrowing $borrowing)
    {
        abort_unless($borrowing->user_id === auth()->id(), 403);

        $borrowing->load(['items.tool', 'user']);

        return view('borrowings.show', compact('borrowing'));
    }

    public function create()
    {
        $cart = session('cart', []);
        $items = [];
        $totalItems = 0;
        $totalPerDay = 0;

        foreach ($cart as $toolId => $quantity) {
            $tool = Tool::find($toolId);

            if (! $tool || ! $tool->is_active || $quantity > $tool->available_stock) {
                continue;
            }

            $items[] = [
                'tool' => $tool,
                'quantity' => $quantity,
                'subtotal_per_day' => $tool->price_per_day * $quantity,
            ];
            $totalItems += $quantity;
            $totalPerDay += $tool->price_per_day * $quantity;
        }

        if (empty($items)) {
            return redirect()->route('tools.index')
                ->with('error', 'Keranjang peminjaman Anda kosong. Silakan pilih alat terlebih dahulu.');
        }

        return view('borrowings.create', compact('items', 'totalItems', 'totalPerDay'));
    }

    public function store(Request $request)
    {
        $cart = session('cart', []);
        $cart = array_filter($cart, fn ($qty) => $qty > 0);

        if (empty($cart)) {
            return redirect()->route('tools.index')
                ->with('error', 'Keranjang peminjaman Anda kosong.');
        }

        $validated = $request->validate([
            'borrower_type' => ['required', Rule::in([Borrowing::TYPE_INTERNAL, Borrowing::TYPE_EKSTERNAL])],
            'nim_nip' => ['required', 'string', 'max:50'],
            'institution' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:2000'],
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after:borrow_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $userName = \Illuminate\Support\Str::slug(auth()->user()->name);
        $uniqueSuffix = time().'-'.Str::random(6);
        $ext = $request->hasFile('document') ? strtolower($request->file('document')->getClientOriginalExtension()) : null;

        $documentPath = $request->hasFile('document')
            ? $request->file('document')->storeAs('borrowing-documents', 'dokumen-peminjaman-'.$userName.'-'.$uniqueSuffix.'.'.$ext)
            : null;

        $tools = Tool::active()->whereIn('id', array_keys($cart))->get();

        if ($tools->isEmpty()) {
            return back()->with('error', 'Alat yang dipilih tidak tersedia.');
        }

        $borrowing = DB::transaction(function () use ($cart, $tools, $validated, $documentPath) {
            $borrowing = Borrowing::create([
                'user_id' => auth()->id(),
                'code' => $this->generateCode(),
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => Borrowing::STATUS_PENDING,
                'payment_status' => Borrowing::PAYMENT_UNPAID,
                'borrower_type' => $validated['borrower_type'],
                'nim_nip' => $validated['nim_nip'],
                'institution' => $validated['institution'],
                'purpose' => $validated['purpose'],
                'borrow_date' => $validated['borrow_date'],
                'return_date' => $validated['return_date'],
                'notes' => $validated['notes'] ?? null,
                'document_path' => $documentPath,
            ]);

            foreach ($tools as $tool) {
                $quantity = $cart[$tool->id];

                if ($quantity > $tool->available_stock) {
                    throw new \RuntimeException("Stok {$tool->name} tidak mencukupi.");
                }

                BorrowingItem::create([
                    'borrowing_id' => $borrowing->id,
                    'tool_id' => $tool->id,
                    'quantity' => $quantity,
                    'price_per_day' => $tool->price_per_day,
                ]);
            }

            return $borrowing;
        });

        session()->forget('cart');

        // Beri tahu admin bahwa ada peminjaman baru menunggu persetujuan.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                'Peminjaman Baru',
                "Peminjaman {$borrowing->code} diajukan oleh ".auth()->user()->name.' dan menunggu persetujuan.',
                route('admin.borrowings.show', $borrowing),
                notifyViaEmail: true,
            ));
        }

        return redirect()->route('borrowings.show', $borrowing)
            ->with('success', "Peminjaman {$borrowing->code} berhasil diajukan dan menunggu persetujuan.");
    }

    public function cancel(Borrowing $borrowing)
    {
        abort_unless($borrowing->user_id === auth()->id(), 403);
        abort_unless(in_array($borrowing->status, [Borrowing::STATUS_PENDING, Borrowing::STATUS_APPROVED]), 403);

        DB::transaction(function () use ($borrowing) {
            // Kembalikan stok bila peminjaman sudah disetujui (stok berkurang saat approval).
            if ($borrowing->status === Borrowing::STATUS_APPROVED) {
                $borrowing->load('items.tool');

                foreach ($borrowing->items as $item) {
                    $item->tool->increment('available_stock', $item->quantity);
                }
            }

            $borrowing->update([
                'status' => Borrowing::STATUS_CANCELLED,
            ]);
        });

        return back()->with('success', 'Peminjaman dibatalkan.');
    }

    public function invoice(Borrowing $borrowing)
    {
        abort_unless($borrowing->user_id === auth()->id(), 403);

        $borrowing->load(['items.tool', 'user']);

        return view('borrowings.invoice', compact('borrowing'));
    }

    protected function generateCode(): string
    {
        $prefix = 'PNJ-'.date('Ymd');

        do {
            $code = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (Borrowing::where('code', $code)->exists());

        return $code;
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.date('Ymd');

        do {
            $number = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (Borrowing::where('invoice_number', $number)->exists());

        return $number;
    }
}
