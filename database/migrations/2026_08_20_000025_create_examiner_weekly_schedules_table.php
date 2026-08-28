<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examiner_weekly_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7); // YYYY-MM, jadwal berlaku selama bulan tersebut.
            $table->unsignedTinyInteger('day_of_week'); // 1 = Senin ... 7 = Minggu
            $table->timestamps();

            $table->unique(['month', 'day_of_week', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examiner_weekly_schedules');
    }
};
