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
        Schema::create('bench_fee_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('level');
            $table->string('type');
            $table->string('category')->default('biomedis');
            $table->integer('rate');
            $table->timestamps();

            $table->unique(['level', 'type', 'category']);
        });

        // Tarif default bench fee per 3 bulan untuk kategori biomedis dan non-biomedis.
        $rates = [
            ['level' => 'S1', 'type' => 'dalam', 'rate' => 75000],
            ['level' => 'S1', 'type' => 'luar', 'rate' => 100000],
            ['level' => 'S2/S3', 'type' => 'dalam', 'rate' => 150000],
            ['level' => 'S2/S3', 'type' => 'luar', 'rate' => 200000],
        ];

        foreach (['biomedis', 'non-biomedis'] as $category) {
            foreach ($rates as $rate) {
                DB::table('bench_fee_rates')->insert([
                    'id' => Str::uuid(),
                    'level' => $rate['level'],
                    'type' => $rate['type'],
                    'category' => $category,
                    'rate' => $rate['rate'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bench_fee_rates');
    }
};
