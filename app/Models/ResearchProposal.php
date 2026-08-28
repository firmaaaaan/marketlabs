<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ResearchProposal extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'code',
        'title',
        'field',
        'description',
        'objectives',
        'institution',
        'nim_nip',
        'customer_type',
        'start_date',
        'end_date',
        'status',
        'admin_notes',
        'document_path',
        'letter_path',
        'replacement_letter_path',
        'bench_fee',
        'bench_fee_level',
        'bench_fee_type',
        'bench_fee_category',
        'needs_laboran',
        'laboratorium_id',
        'laboran_id',
        'laboran_fee',
        'penalty',
        'penalty_note',
        'processed_at',
        'approved_at',
        'ongoing_at',
        'done_at',
        'rejected_at',
        'payment_status',
        'invoice_number',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'ongoing_at' => 'datetime',
            'done_at' => 'datetime',
            'rejected_at' => 'datetime',
            'needs_laboran' => 'boolean',
            'laboran_fee' => 'integer',
            'penalty' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->hasMany(ResearchProposalMember::class);
    }

    public function logbooks()
    {
        return $this->hasMany(ResearchLogbook::class)->latest('log_date');
    }

    public function tools()
    {
        return $this->belongsToMany(Tool::class, 'research_proposal_tools')
            ->withPivot('quantity', 'days');
    }

    public function laboran()
    {
        return $this->belongsTo(User::class, 'laboran_id');
    }

    public function laboratorium()
    {
        return $this->belongsTo(Laboratorium::class, 'laboratorium_id');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_ONGOING => 'Sedang Berlangsung',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_DONE => 'Selesai',
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
     * Nama file dokumen pendukung (tanpa path).
     */
    public function getDocumentNameAttribute(): ?string
    {
        if (! $this->document_path) {
            return null;
        }

        return basename($this->document_path);
    }

    public function getLetterNameAttribute(): ?string
    {
        return $this->letter_path ? basename($this->letter_path) : null;
    }

    public function getReplacementLetterNameAttribute(): ?string
    {
        return $this->replacement_letter_path ? basename($this->replacement_letter_path) : null;
    }

    /**
     * Kategori penelitian untuk tarif bench fee.
     *
     * @return array<string, string>
     */
    public static function benchFeeCategories(): array
    {
        return [
            'biomedis' => 'Biomedis',
            'non-biomedis' => 'Non-Biomedis',
        ];
    }

    /**
     * Jenis customer pemohon (informasi saja, tidak memengaruhi biaya).
     *
     * @return array<string, string>
     */
    public static function customerTypes(): array
    {
        return [
            'dosentendik' => 'Dosen / Tendik',
            'mahasiswa' => 'Mahasiswa',
            'siswa' => 'Siswa',
        ];
    }

    public static function customerTypeLabel(string $type): string
    {
        return self::customerTypes()[$type] ?? $type;
    }

    public static function benchFeeCategoryLabel(string $category): string
    {
        return self::benchFeeCategories()[$category] ?? $category;
    }

    /**
     * Tarif bench fee per 3 bulan: jenjang (S1 / S2/S3) × jenis instansi (dalam/luar)
     * × kategori (biomedis / non-biomedis).
     *
     * Diambil dari tabel bench_fee_rates yang bisa diperbarui admin; bila belum ada
     * data, kembali ke tarif default.
     *
     * @return array<string, array<string, array<string, int>>>
     */
    public static function benchFeeRates(): array
    {
        $defaults = [
            'S1' => [
                'dalam' => ['biomedis' => 75000, 'non-biomedis' => 75000],
                'luar' => ['biomedis' => 100000, 'non-biomedis' => 100000],
            ],
            'S2/S3' => [
                'dalam' => ['biomedis' => 150000, 'non-biomedis' => 150000],
                'luar' => ['biomedis' => 200000, 'non-biomedis' => 200000],
            ],
        ];

        $rates = BenchFeeRate::all();

        if ($rates->isEmpty()) {
            return $defaults;
        }

        $result = $defaults;

        foreach ($rates as $rate) {
            $result[$rate->level][$rate->type][$rate->category] = (int) $rate->rate;
        }

        return $result;
    }

    public static function benchFeeTypeLabel(string $type): string
    {
        return $type === 'dalam' ? 'Dalam' : 'Luar';
    }

    /**
     * Hitung bench fee: tarif per 3 bulan × jumlah periode (dibulatkan ke atas).
     */
    public static function calculateBenchFee(string $level, string $type, string $category, string $startDate, string $endDate): int
    {
        $rate = self::benchFeeRates()[$level][$type][$category] ?? 0;

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Jumlah bulan (inklusi tanggal mulai & selesai).
        $months = (($end->year - $start->year) * 12) + ($end->month - $start->month) + 1;
        $months = max(1, $months);

        // Setiap 3 bulan = 1 periode tarif.
        $periods = (int) ceil($months / 3);

        return $rate * $periods;
    }

    public function getBenchFeeLevelLabelAttribute(): ?string
    {
        return $this->bench_fee_level ?? null;
    }

    public function getBenchFeeTypeLabelAttribute(): ?string
    {
        return $this->bench_fee_type ? self::benchFeeTypeLabel($this->bench_fee_type) : null;
    }

    public function getBenchFeeCategoryLabelAttribute(): ?string
    {
        return $this->bench_fee_category ? self::benchFeeCategoryLabel($this->bench_fee_category) : null;
    }

    public function getCustomerTypeLabelAttribute(): ?string
    {
        return $this->customer_type ? self::customerTypeLabel($this->customer_type) : null;
    }

    public function getFormattedBenchFeeAttribute(): ?string
    {
        return $this->bench_fee !== null ? 'Rp '.number_format($this->bench_fee, 0, ',', '.') : null;
    }

    public function getFormattedLaboranFeeAttribute(): ?string
    {
        return $this->laboran_fee !== null ? 'Rp '.number_format($this->laboran_fee, 0, ',', '.') : null;
    }

    public function getFormattedPenaltyAttribute(): string
    {
        return 'Rp '.number_format($this->penalty, 0, ',', '.');
    }

    /**
     * Lama penelitian dalam hari (selisih tanggal), minimal 1.
     */
    public function getDurationDaysAttribute(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }

        return max(1, $this->start_date->diffInDays($this->end_date));
    }

    /**
     * Total sewa alat: harga/hari × jumlah × hari penggunaan per alat.
     */
    public function getToolsSubtotalAttribute(): int
    {
        return $this->tools->sum(fn ($tool) => $tool->price_per_day * $tool->pivot->quantity * $tool->pivot->days);
    }

    public function getFormattedToolsSubtotalAttribute(): string
    {
        return 'Rp '.number_format($this->tools_subtotal, 0, ',', '.');
    }

    /**
     * Total keseluruhan biaya: sewa alat + bench fee + biaya laboran + denda.
     */
    public function getGrandTotalAttribute(): int
    {
        return $this->tools_subtotal + (int) $this->bench_fee + (int) $this->laboran_fee + (int) $this->penalty;
    }

    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp '.number_format($this->grand_total, 0, ',', '.');
    }
}
