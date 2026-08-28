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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('role')->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('testimonials')->insert([
            [
                'id' => Str::uuid(),
                'name' => 'Dr. Ratna Dewi',
                'role' => 'Peneliti · Universitas Padjadjaran',
                'quote' => 'Pengajuan riset jadi jauh lebih cepat. Saya bisa memantau progres penelitian dan mengisi logbook harian langsung dari satu platform, tanpa bolak-balik ke laboratorium.',
                'rating' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Budi Santoso',
                'role' => 'Laboran · Laboratorium Kimia',
                'quote' => 'Manajemen peminjaman alat jadi rapi. Stok, jadwal, dan biaya sewa dihitung otomatis, jadi tidak ada lagi salah hitung atau alat yang bentrok pemakaiannya.',
                'rating' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Sari Wulandari',
                'role' => 'Mahasiswa S2 · ITB',
                'quote' => 'Pengujian sampel sangat mudah dilakukan. Pilih parameter, langsung masuk keranjang, dan invoice resminya bisa langsung diunduh. Sangat membantu untuk penelitian saya.',
                'rating' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
