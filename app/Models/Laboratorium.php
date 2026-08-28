<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Laboratorium extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'laboratoriums';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function researchProposals()
    {
        return $this->hasMany(ResearchProposal::class, 'laboratorium_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
