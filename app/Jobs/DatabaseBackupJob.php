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

class DatabaseBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public ?array $selectedTables,
        public string $userId,
    ) {}

    public function handle(): void
    {
        $tables = $this->getDatabaseTables($this->selectedTables);

        if (empty($tables)) {
            throw new \InvalidArgumentException('Tidak ada tabel yang dipilih.');
        }

        $data = [
            'metadata' => [
                'app_name' => config('app.name', 'MarketLabs'),
                'app_version' => '1.0',
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'db_driver' => config('database.default'),
                'created_at' => now()->toIso8601String(),
                'created_by' => User::find($this->userId)?->name ?? 'System',
                'table_count' => count($tables),
            ],
            'tables' => [],
        ];

        $sensitiveColumns = ['password', 'remember_token'];

        foreach ($tables as $tableName => $rowCount) {
            $rows = DB::table($tableName)->get()->each(function ($row) use ($sensitiveColumns) {
                foreach ($sensitiveColumns as $col) {
                    if (isset($row->$col)) {
                        $row->$col = null;
                    }
                }
            })->toArray();
            $data['tables'][$tableName] = [
                'row_count' => count($rows),
                'columns' => count($rows) > 0 ? array_keys((array) $rows[0]) : [],
                'data' => $rows,
            ];
        }

        $tableNames = array_keys($tables);
        if ($this->selectedTables === null || empty($this->selectedTables)) {
            $tableSlug = 'semua_tabel';
        } elseif (count($tableNames) === 1) {
            $tableSlug = $tableNames[0];
        } else {
            $tableSlug = implode('_', array_slice($tableNames, 0, 5));
            if (count($tableNames) > 5) {
                $tableSlug .= '_dan_lainnya';
            }
        }

        $filename = 'backup_'.$tableSlug.'_'.now()->format('Y-m-d_H-i-s').'.json';
        $path = 'backups/'.$filename;

        Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $fileSize = Storage::disk('local')->size($path);

        ActivityLog::create([
            'user_id' => $this->userId,
            'user_name' => User::find($this->userId)?->name ?? 'System',
            'role' => User::find($this->userId)?->role ?? 'admin',
            'action' => 'backup_create',
            'description' => 'Backup database dibuat: '.$filename,
            'properties' => [
                'filename' => $filename,
                'file_size' => $fileSize,
                'tables' => array_keys($tables),
            ],
            'ip_address' => null,
        ]);

        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new EventNotification(
                'Backup Selesai',
                "Backup database berhasil dibuat: {$filename}",
                url: null,
                notifyViaEmail: false,
            ));
        }
    }

    public function failed(\Throwable $exception): void
    {
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new EventNotification(
                'Backup Gagal',
                "Backup database gagal: {$exception->getMessage()}",
                notifyViaEmail: false,
            ));
        }
    }

    protected function getDatabaseTables(?array $selected = null): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $allTables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $key = 'name';
        } else {
            $allTables = DB::select('SHOW TABLES');
            $dbName = DB::getDatabaseName();
            $key = "Tables_in_{$dbName}";
        }

        $tables = [];
        foreach ($allTables as $row) {
            $name = $row->$key;
            if ($selected === null || in_array($name, $selected)) {
                $tables[$name] = DB::table($name)->count();
            }
        }

        return $tables;
    }
}
