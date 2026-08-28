<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowingItem extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'borrowing_id',
        'tool_id',
        'quantity',
        'price_per_day',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_per_day' => 'integer',
        ];
    }

    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }
}
