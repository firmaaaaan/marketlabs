<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HealthCheckup extends Model
{
    use HasUuids, LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DONE = 'done';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'type_id',
        'examiner_id',
        'code',
        'booking_date',
        'queue_number',
        'purpose',
        'status',
        'result',
        'result_notes',
        'result_file',
        'payment_status',
        'invoice_number',
        'processed_at',
        'approved_at',
        'done_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'queue_number' => 'integer',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'done_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(HealthTestType::class, 'type_id');
    }

    public function examiner()
    {
        return $this->belongsTo(User::class, 'examiner_id');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Menunggu Konfirmasi',
            self::STATUS_APPROVED => 'Terjadwal',
            self::STATUS_DONE => 'Selesai',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => $status,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
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

    /**
     * Nomor antrian berformat, mis. 1 → "Q-001".
     */
    public function getQueueLabelAttribute(): string
    {
        return 'Q-'.str_pad((string) $this->queue_number, 3, '0', STR_PAD_LEFT);
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->type?->formatted_price ?? 'Rp 0';
    }
}
