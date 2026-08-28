<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class Setting extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Ambil nilai pengaturan berdasarkan key.
     * Aman dipanggil dari view: bila tabel belum ada, kembalikan default tanpa error.
     */
    public static function get(string $key, $default = null)
    {
        try {
            return static::query()->where('key', $key)->value('value') ?? $default;
        } catch (Throwable $e) {
            Log::warning("Gagal membaca pengaturan '{$key}': {$e->getMessage()}");

            return $default;
        }
    }

    /**
     * Simpan (atau perbarui) sebuah pengaturan.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value],
        );
    }
}
