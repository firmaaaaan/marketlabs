<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BenchFeeRate extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'level',
        'type',
        'category',
        'rate',
    ];
}
