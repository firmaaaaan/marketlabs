<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleTestItem extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'sample_test_id',
        'parameter_id',
        'sample_name',
        'sample_description',
        'quantity',
        'sample_form_id',
        'sample_type_id',
        'rate',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'rate' => 'integer',
        ];
    }

    public function test()
    {
        return $this->belongsTo(SampleTest::class, 'sample_test_id');
    }

    public function parameter()
    {
        return $this->belongsTo(TestParameter::class, 'parameter_id');
    }

    public function sampleForm()
    {
        return $this->belongsTo(SampleForm::class, 'sample_form_id');
    }

    public function sampleType()
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    /**
     * Subtotal item = tarif layanan × jumlah sampel pada baris ini.
     */
    public function getSubtotalAttribute(): int
    {
        return $this->rate * $this->quantity;
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp '.number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormLabelAttribute(): ?string
    {
        return $this->sampleForm?->name;
    }

    public function getTypeLabelAttribute(): ?string
    {
        return $this->sampleType?->name;
    }
}
