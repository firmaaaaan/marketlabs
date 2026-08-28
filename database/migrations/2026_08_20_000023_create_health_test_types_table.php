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
        Schema::create('health_test_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('health_test_types')->insert([
            [
                'id' => Str::uuid(),
                'key' => 'hbsag',
                'name' => 'Pemeriksaan HbSAg',
                'description' => 'Deteksi antigen permukaan Hepatitis B (HBsAg) dalam darah. Umumnya untuk persyaratan kerja, imigrasi, atau pemeriksaan rutin.',
                'price' => 85000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'key' => 'narkoba',
                'name' => 'Pemeriksaan Narkoba',
                'description' => 'Skrining penyalahgunaan narkoba melalui sampel urine (metamfetamin, opiat, ganja, dll). Umumnya untuk persyaratan kerja atau pendidikan.',
                'price' => 150000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('health_test_types');
    }
};
