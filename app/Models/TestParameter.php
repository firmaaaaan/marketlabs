<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestParameter extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'name',
        'method',
        'unit_id',
        'rate',
        'description',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function unit()
    {
        return $this->belongsTo(SampleUnit::class, 'unit_id');
    }

    public function sampleTestItems()
    {
        return $this->hasMany(SampleTestItem::class, 'parameter_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedRateAttribute(): string
    {
        return 'Rp '.number_format($this->rate, 0, ',', '.');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}
