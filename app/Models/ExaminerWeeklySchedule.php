<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExaminerWeeklySchedule extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'examiner_weekly_schedules';

    protected $fillable = [
        'user_id',
        'month', // YYYY-MM
        'day_of_week', // 1 = Senin ... 7 = Minggu
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
