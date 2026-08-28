<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasUuids, LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REGISTERED = 'registered';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'event_id',
        'user_id',
        'registered_by',
        'status',
        'answers',
        'attendance_token',
        'attendance_answers',
        'attended_at',
        'certificate_number',
        'certificate_path',
        'certificate_back_path',
        'certificate_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'attendance_answers' => 'array',
            'attended_at' => 'datetime',
            'certificate_generated_at' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User yang mendaftarkan atas nama peserta ini (proxy), jika ada.
     */
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function getIsProxyAttribute(): bool
    {
        return $this->registered_by !== null;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Menunggu Konfirmasi',
            self::STATUS_REGISTERED => 'Terdaftar',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => $status,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function getIsAttendedAttribute(): bool
    {
        return $this->attended_at !== null;
    }

    public function getHasCertificateAttribute(): bool
    {
        return $this->certificate_number !== null && $this->certificate_path !== null;
    }

    public function getHasCertificateBackAttribute(): bool
    {
        return $this->certificate_back_path !== null;
    }
}
