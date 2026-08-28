<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'name',
        'role',
        'quote',
        'rating',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Inisial dari nama, mis. "Dr. Ratna Dewi" → "RD".
     */
    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));

        if (empty($parts)) {
            return '?';
        }

        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1));

        if (count($parts) > 1) {
            $initials .= mb_strtoupper(mb_substr(end($parts), 0, 1));
        }

        return $initials;
    }
}
