<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleTest extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_TESTING = 'testing';

    public const STATUS_DONE = 'done';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    public const DELIVERY_DIRECT = 'direct';

    public const DELIVERY_PACKAGED = 'packaged';

    protected $fillable = [
        'user_id',
        'laboran_id',
        'code',
        'notes',
        'delivery_method',
        'status',
        'result',
        'result_notes',
        'result_file',
        'total_cost',
        'payment_status',
        'invoice_number',
        'processed_at',
        'approved_at',
        'received_at',
        'tested_at',
        'done_at',
        'rejected_at',
    ];

    public static function deliveryMethodLabel(?string $method): string
    {
        return match ($method) {
            self::DELIVERY_DIRECT => 'Diantar Langsung',
            self::DELIVERY_PACKAGED => 'Dipaketkan (Dikirim)',
            default => '-',
        };
    }

    public function getDeliveryMethodLabelAttribute(): string
    {
        return self::deliveryMethodLabel($this->delivery_method);
    }

    protected function casts(): array
    {
        return [
            'total_cost' => 'integer',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
            'tested_at' => 'datetime',
            'done_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laboran()
    {
        return $this->belongsTo(User::class, 'laboran_id');
    }

    public function items()
    {
        return $this->hasMany(SampleTestItem::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_RECEIVED => 'Sampel Diterima',
            self::STATUS_TESTING => 'Sedang Diuji',
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

    public function getFormattedTotalCostAttribute(): string
    {
        return 'Rp '.number_format($this->total_cost, 0, ',', '.');
    }

    /**
     * Daftar satuan unik dari parameter semua item (mis. "Sampel, Running").
     */
    public function getUnitsLabelAttribute(): string
    {
        $units = $this->items->map(fn ($item) => $item->parameter?->unit?->name)->filter()->unique();

        return $units->isNotEmpty() ? $units->implode(', ') : '-';
    }

    /**
     * Total jumlah sampel = jumlah quantity semua item.
     */
    public function getTotalSamplesAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Jumlah layanan (parameter) berbeda yang dipakai pengujian ini.
     */
    public function getServicesCountAttribute(): int
    {
        return $this->items->groupBy('parameter_id')->count();
    }

    /**
     * Hitung ulang total biaya dari item (tarif × jumlah per baris sampel).
     */
    public function recalculateTotalCost(): int
    {
        $total = $this->items->sum(fn ($item) => $item->rate * $item->quantity);

        $this->update(['total_cost' => $total]);

        return $total;
    }
}
