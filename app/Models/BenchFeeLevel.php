<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenchFeeLevel extends Model
{
    protected $fillable = ['name', 'label', 'sort_order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Return options array for radio buttons / selects.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return static::ordered()->pluck('label', 'name')->toArray();
    }

    /**
     * Return all level names as array.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return static::pluck('name')->toArray();
    }
}
