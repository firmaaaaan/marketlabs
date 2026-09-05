<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasUuids, LogsActivity;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_COMPLETED = 'completed';

    public const MODE_ONLINE = 'online';

    public const MODE_OFFLINE = 'offline';

    public const MODE_HYBRID = 'hybrid';

    public static function modes(): array
    {
        return [
            self::MODE_ONLINE => 'Online',
            self::MODE_OFFLINE => 'Offline',
            self::MODE_HYBRID => 'Hybrid',
        ];
    }

    public static function modeLabel(string $mode): string
    {
        return self::modes()[$mode] ?? $mode;
    }

    protected $fillable = [
        'code',
        'slug',
        'title',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'quota',
        'fee',
        'discount',
        'registration_deadline',
        'status',
        'mode',
        'image',
        'poster',
        'form_fields',
        'attendance_fields',
        'certificate_template',
        'certificate_template_back',
        'certificate_font',
        'certificate_layout',
        'certificate_layout_back',
        'certificate_batch_status',
        'certificate_batch_total',
        'certificate_batch_done',
        'created_by',
        'attendance_enabled',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'quota' => 'integer',
            'fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'registration_deadline' => 'datetime',
            'form_fields' => 'array',
            'attendance_fields' => 'array',
            'certificate_layout' => 'array',
            'certificate_layout_back' => 'array',
            'certificate_batch_total' => 'integer',
            'certificate_batch_done' => 'integer',
            'attendance_enabled' => 'boolean',
        ];
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_CLOSED => 'Ditutup',
            self::STATUS_COMPLETED => 'Selesai',
            default => $status,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function getModeLabelAttribute(): string
    {
        return $this->mode ? self::modeLabel($this->mode) : null;
    }

    public function getHasFeeAttribute(): bool
    {
        return $this->fee !== null && (float) $this->fee > 0;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount !== null && (float) $this->discount > 0;
    }

    public function getEffectiveFeeAttribute(): float
    {
        return max(0, (float) $this->fee - (float) $this->discount);
    }

    public function getDiscountPercentAttribute(): ?float
    {
        if (! $this->has_fee || ! $this->has_discount) {
            return null;
        }

        return min(100, round((float) $this->discount / (float) $this->fee * 100, 1));
    }

    public function getFeeLabelAttribute(): string
    {
        if (! $this->has_fee) {
            return 'Gratis';
        }

        return 'Rp '.number_format((float) $this->fee, 0, ',', '.');
    }

    public function getEffectiveFeeLabelAttribute(): string
    {
        if (! $this->has_fee) {
            return 'Gratis';
        }

        return 'Rp '.number_format($this->effective_fee, 0, ',', '.');
    }

    public function getDiscountLabelAttribute(): ?string
    {
        if (! $this->has_discount) {
            return null;
        }

        return 'Rp '.number_format((float) $this->discount, 0, ',', '.');
    }

    public function getRegistrationCountAttribute(): int
    {
        return $this->registrations()
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->count();
    }

    /**
     * Event masih terbuka untuk pendaftaran (aktif, belum lewat deadline, kuota belum penuh).
     */
    public function getIsOpenAttribute(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return false;
        }

        if ($this->quota !== null && $this->registration_count >= $this->quota) {
            return false;
        }

        return true;
    }

    public function getQuotaRemainingAttribute(): ?int
    {
        if ($this->quota === null) {
            return null;
        }

        return max(0, $this->quota - $this->registration_count);
    }

    public function isRegisteredBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->registrations()
            ->where('user_id', $user->id)
            ->whereIn('status', [EventRegistration::STATUS_PENDING, EventRegistration::STATUS_REGISTERED])
            ->exists();
    }

    public function getCertificateReadyAttribute(): bool
    {
        return $this->certificate_template !== null && $this->certificate_layout !== null;
    }

    public function getCertificateBatchPercentageAttribute(): ?int
    {
        if ($this->certificate_batch_total <= 0) {
            return null;
        }

        return (int) round(($this->certificate_batch_done / $this->certificate_batch_total) * 100);
    }

    public function getIsCertificateBatchProcessingAttribute(): bool
    {
        return $this->certificate_batch_status === 'processing';
    }

    /**
     * Sisi belakang aktif bila template belakang diunggah.
     */
    public function getHasCertificateBackAttribute(): bool
    {
        return $this->certificate_template_back !== null;
    }
}
