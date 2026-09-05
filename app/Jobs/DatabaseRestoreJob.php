<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseRestoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(
        public string $filePath,
        public string $userId,
    ) {}

    public function handle(): void
    {
        $content = Storage::disk('local')->get($this->filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('File backup tidak valid: '.json_last_error_msg());
        }

        if (! isset($data['tables']) || ! is_array($data['tables'])) {
            throw new \InvalidArgumentException('Format file backup tidak sesuai.');
        }

        $restored = 0;
        $skipped = 0;
        $errors = [];

        foreach ($data['tables'] as $tableName => $tableData) {
            if (! $this->isValidTable($tableName)) {
                $skipped++;

                continue;
            }

            try {
                $driver = DB::getDriverName();

                DB::beginTransaction();

                if ($driver === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::table($tableName)->truncate();
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } elseif ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = OFF');
                    DB::table($tableName)->truncate();
                    DB::statement('PRAGMA foreign_keys = ON');
                } else {
                    DB::table($tableName)->truncate();
                }

                if (! empty($tableData['data']) && is_array($tableData['data'])) {
                    $chunks = array_chunk($tableData['data'], 1000);
                    foreach ($chunks as $chunk) {
                        DB::table($tableName)->insert(array_map(fn ($row) => (array) $row, $chunk));
                    }
                }

                DB::commit();
                $restored++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "{$tableName}: {$e->getMessage()}";
            }
        }

        $metadata = $data['metadata'] ?? [];
        $filename = basename($this->filePath);

        ActivityLog::create([
            'user_id' => $this->userId,
            'user_name' => User::find($this->userId)?->name ?? 'System',
            'role' => User::find($this->userId)?->role ?? 'admin',
            'action' => 'backup_restore',
            'description' => "Database di-restore dari backup: {$filename} ({$restored} tabel)",
            'properties' => [
                'filename' => $filename,
                'tables_count' => $restored,
                'skipped' => $skipped,
                'errors' => $errors,
                'source' => $metadata['created_by'] ?? 'unknown',
            ],
            'ip_address' => null,
        ]);

        $user = User::find($this->userId);
        if ($user) {
            $msg = "Restore selesai: {$restored} tabel dipulihkan.";
            if ($skipped > 0) {
                $msg .= " {$skipped} tabel dilewati.";
            }
            if (! empty($errors)) {
                $msg .= ' Error: '.implode('; ', array_slice($errors, 0, 3));
            }

            $user->notify(new EventNotification(
                'Restore Selesai',
                $msg,
                url: null,
                notifyViaEmail: false,
            ));
        }

        @unlink($this->filePath);
    }

    public function failed(\Throwable $exception): void
    {
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new EventNotification(
                'Restore Gagal',
                "Restore database gagal: {$exception->getMessage()}",
                notifyViaEmail: false,
            ));
        }

        @unlink($this->filePath);
    }

    protected function isValidTable(string $tableName): bool
    {
        $excluded = ['migrations', 'jobs', 'failed_jobs', 'cache', 'sessions', 'password_reset_tokens', 'sqlite_sequence'];

        if (in_array($tableName, $excluded)) {
            return false;
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            return false;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $exists = DB::select("SELECT COUNT(*) as cnt FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);
        } else {
            $dbName = DB::getDatabaseName();
            $exists = DB::select('SELECT COUNT(*) as cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$dbName, $tableName]);
        }

        return $exists[0]->cnt > 0;
    }
}
