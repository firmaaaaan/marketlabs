<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role 'petugas' dihapus karena fungsinya sama dengan laboran.
     * Seluruh user ber-role petugas dikonversi menjadi laboran.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'petugas')->update(['role' => 'laboran']);
    }

    public function down(): void
    {
        // Tidak dapat memisahkan kembali user yang sudah dikonversi secara otomatis.
    }
};