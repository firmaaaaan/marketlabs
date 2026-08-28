<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insertOrIgnore([
            ['id' => Str::uuid(), 'key' => 'whatsapp_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'whatsapp_number', 'value' => '6281234567890', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'whatsapp_message', 'value' => 'Halo Admin MarketLabs, saya ingin bertanya tentang layanan laboratorium.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_enabled', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_days', 'value' => '1,2,3,4,5', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_open_time', 'value' => '08:00', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_close_time', 'value' => '14:00', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_quota', 'value' => '30', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_auto_assign', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_duration', 'value' => '5', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_break_start', 'value' => '12:00', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'schedule_break_end', 'value' => '13:00', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'footer_address', 'value' => 'Jl. Laboratorium Teknologi No. 123, Bandung, Jawa Barat', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'footer_phone', 'value' => '+6281234567890', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'key' => 'footer_email', 'value' => 'info@marketlabs.id', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
