<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_INTERNAL = 'internal';

    public const TYPE_EKSTERNAL = 'eksternal';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'code',
        'invoice_number',
        'status',
        'payment_status',
        'borrower_type',
        'nim_nip',
        'institution',
        'purpose',
        'borrow_date',
        'return_date',
        'discount',
        'penalty',
        'pickup_notes',
        'notes',
        'rejection_reason',
        'document_path',
        'processed_at',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'return_date' => 'date',
            'processed_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_BORROWED => 'Dipinjam',
            self::STATUS_RETURNED => 'Dikembalikan',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => $status,
        };
    }

    public static function typeLabel(string $type): string
    {
        return $type === self::TYPE_INTERNAL ? 'Internal' : 'Eksternal';
    }

    public static function paymentStatusLabel(string $status): string
    {
        return $status === self::PAYMENT_PAID ? 'Lunas' : 'Belum Dibayar';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::paymentStatusLabel($this->payment_status);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function getBorrowerTypeLabelAttribute(): string
    {
        return self::typeLabel($this->borrower_type);
    }

    /**
     * Nama file dokumen pendukung (tanpa path).
     */
    public function getDocumentNameAttribute(): ?string
    {
        if (! $this->document_path) {
            return null;
        }

        return basename($this->document_path);
    }

    /**
     * Lama peminjaman dalam hari (minimal 1).
     */
    public function getDurationDaysAttribute(): int
    {
        if (! $this->borrow_date || ! $this->return_date) {
            return 0;
        }

        return max(1, $this->borrow_date->diffInDays($this->return_date));
    }

    /**
     * Biaya dasar peminjaman (harga snapshot × jumlah × lama hari) sebelum diskon & denda.
     */
    public function getBaseCostAttribute(): int
    {
        $days = $this->duration_days;

        return $this->items->sum(fn ($item) => $item->price_per_day * $item->quantity * $days);
    }

    /**
     * Nilai diskon dalam Rupiah (persen dari biaya dasar).
     */
    public function getDiscountAmountAttribute(): int
    {
        return (int) round($this->base_cost * (int) $this->discount / 100);
    }

    /**
     * Total biaya peminjaman: biaya dasar − diskon + denda.
     */
    public function getTotalCostAttribute(): int
    {
        return max(0, $this->base_cost - $this->discount_amount + (int) $this->penalty);
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return 'Rp '.number_format($this->total_cost, 0, ',', '.');
    }

    public function getFormattedBaseCostAttribute(): string
    {
        return 'Rp '.number_format($this->base_cost, 0, ',', '.');
    }

    public function getFormattedDiscountAmountAttribute(): string
    {
        return 'Rp '.number_format($this->discount_amount, 0, ',', '.');
    }

    public function getFormattedPenaltyAttribute(): string
    {
        return 'Rp '.number_format($this->penalty, 0, ',', '.');
    }
}
