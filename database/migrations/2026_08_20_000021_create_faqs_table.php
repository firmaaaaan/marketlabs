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
        Schema::create('faqs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('faqs')->insert([
            [
                'id' => Str::uuid(),
                'question' => 'Bagaimana cara melakukan booking pemeriksaan kesehatan?',
                'answer' => 'Login ke akun Anda, buka menu Pemeriksaan Kesehatan, pilih jenis pemeriksaan dan tanggal kedatangan. Nomor antrian akan diberikan otomatis per hari.',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question' => 'Bagaimana cara mengajukan peminjaman alat laboratorium?',
                'answer' => 'Pilih alat yang tersedia di katalog, tambahkan ke keranjang, lalu selesaikan checkout dengan mengisi tujuan dan tanggal peminjaman. Admin akan mengonfirmasi pengajuan Anda.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question' => 'Bagaimana cara mengajukan permohonan riset atau penelitian?',
                'answer' => 'Ajukan melalui menu Riset & Penelitian dengan melengkapi judul, tim, alat, dan surat pendukung. Status pengajuan dapat dipantau di halaman riwayat Anda.',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question' => 'Apakah saya bisa membatalkan booking pemeriksaan?',
                'answer' => 'Bisa. Selama status booking masih Menunggu Konfirmasi atau Terjadwal, Anda dapat membatalkannya dari halaman detail booking.',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question' => 'Bagaimana cara melihat hasil pemeriksaan kesehatan?',
                'answer' => 'Hasil muncul di detail booking dengan status Selesai. Anda juga dapat mengunduh Surat Hasil Pemeriksaan langsung dari halaman tersebut.',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question' => 'Bagaimana cara melacak status pengajuan saya?',
                'answer' => 'Semua pengajuan (booking, peminjaman, riset) dapat dipantau di halaman riwayat masing-masing menu. Notifikasi akan dikirim ke email akun Anda setiap kali status berubah.',
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
