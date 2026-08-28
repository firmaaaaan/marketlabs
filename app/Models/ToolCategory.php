<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolCategory extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'name',
    ];

    public function tools()
    {
        return $this->hasMany(Tool::class, 'category_id');
    }
}
