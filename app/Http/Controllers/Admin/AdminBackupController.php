<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AdminBackupController extends Controller
{
    private string $backupDir = 'backups';

    public function index()
    {
        $backups = $this->getBackupList();

        return view('admin.backup.index', compact('backups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tables'   => 'nullable|array',
            'tables.*' => 'string',
        ]);

        $selectedTables = $request->input('tables');
        $tables = $this->getDatabaseTables($selectedTables);

        if (empty($tables)) {
            return back()->with('error', 'Tidak ada tabel yang dipilih.');
        }

        $data = [
            'metadata' => [
                'app_name'    => config('app.name', 'MarketLabs'),
                'app_version' => '1.0',
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'db_driver'   => config('database.default'),
                'created_at'  => now()->toIso8601String(),
                'created_by'  => auth()->user()->name,
                'table_count' => count($tables),
            ],
            'tables' => [],
        ];

        foreach ($tables as $tableName => $rowCount) {
            $rows = DB::table($tableName)->get()->toArray();
            $data['tables'][$tableName] = [
                'row_count' => count($rows),
                'columns'   => count($rows) > 0 ? array_keys((array) $rows[0]) : [],
                'data'      => $rows,
            ];
        }

        $tableNames = array_keys($tables);
        if (empty($selectedTables)) {
            $tableSlug = 'semua_tabel';
        } elseif (count($tableNames) === 1) {
            $tableSlug = $tableNames[0];
        } else {
            $tableSlug = implode('_', array_slice($tableNames, 0, 5));
            if (count($tableNames) > 5) {
                $tableSlug .= '_dan_lainnya';
            }
        }
        $filename = 'backup_' . $tableSlug . '_' . now()->format('Y-m-d_H-i-s') . '.json';
        $path = $this->backupDir . '/' . $filename;

        Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $fileSize = Storage::disk('local')->size($path);

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'user_name'  => auth()->user()->name,
            'role'       => auth()->user()->role,
            'action'     => 'backup_create',
            'description' => 'Backup database dibuat: ' . $filename,
            'properties' => [
                'filename'  => $filename,
                'file_size' => $fileSize,
                'tables'    => array_keys($tables),
            ],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "Backup berhasil dibuat: {$filename} (" . $this->formatBytes($fileSize) . ')');
    }

    public function download(string $filename)
    {
        $path = $this->backupDir . '/' . $filename;

        if (! Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        $fullPath = Storage::disk('local')->path($path);

        return response()->download($fullPath, $filename, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function destroy(string $filename)
    {
        $path = $this->backupDir . '/' . $filename;

        if (! Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        Storage::disk('local')->delete($path);

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'user_name'  => auth()->user()->name,
            'role'       => auth()->user()->role,
            'action'     => 'backup_delete',
            'description' => 'Backup dihapus: ' . $filename,
            'properties' => ['filename' => $filename],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Backup berhasil dihapus.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:10240',
            'confirm'     => 'required|accepted',
        ]);

        $file = $request->file('backup_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'File backup tidak valid: ' . json_last_error_msg());
        }

        if (! isset($data['tables']) || ! is_array($data['tables'])) {
            return back()->with('error', 'Format file backup tidak sesuai.');
        }

        $restored = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($data['tables'] as $tableName => $tableData) {
                if (! $this->isValidTable($tableName)) {
                    $skipped++;
                    continue;
                }

                try {
                    $driver = DB::getDriverName();

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
                        foreach ($tableData['data'] as $row) {
                            DB::table($tableName)->insert((array) $row);
                        }
                    }

                    $restored++;
                } catch (\Exception $e) {
                    $errors[] = "{$tableName}: {$e->getMessage()}";
                    DB::rollBack();

                    return back()->with('error', 'Gagal restore: ' . implode(', ', $errors));
                }
            }

            DB::commit();

            $metadata = $data['metadata'] ?? [];
            $filename = $file->getClientOriginalName();

            ActivityLog::create([
                'user_id'    => auth()->id(),
                'user_name'  => auth()->user()->name,
                'role'       => auth()->user()->role,
                'action'     => 'backup_restore',
                'description' => "Database di-restore dari backup: {$filename} ({$restored} tabel)",
                'properties' => [
                    'filename'     => $filename,
                    'tables_count' => $restored,
                    'skipped'      => $skipped,
                    'source'       => $metadata['created_by'] ?? 'unknown',
                ],
                'ip_address' => request()->ip(),
            ]);

            $msg = "Restore berhasil: {$restored} tabel dipulihkan.";
            if ($skipped > 0) {
                $msg .= " {$skipped} tabel dilewati.";
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal restore: ' . $e->getMessage());
        }
    }

    public function tables()
    {
        $tables = $this->getDatabaseTables();

        return response()->json($tables);
    }

    private function getDatabaseTables(?array $selected = null): array
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

    private function isValidTable(string $tableName): bool
    {
        $excluded = ['migrations', 'jobs', 'failed_jobs', 'cache', 'sessions', 'password_reset_tokens', 'sqlite_sequence'];

        if (in_array($tableName, $excluded)) {
            return false;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $exists = DB::select("SELECT COUNT(*) as cnt FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);
        } else {
            $dbName = DB::getDatabaseName();
            $exists = DB::select("SELECT COUNT(*) as cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '{$tableName}'");
        }

        return $exists[0]->cnt > 0;
    }

    private function getBackupList(): array
    {
        $dir = Storage::disk('local')->path($this->backupDir);

        if (! is_dir($dir)) {
            Storage::disk('local')->makeDirectory($this->backupDir);

            return [];
        }

        $files = Storage::disk('local')->files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if (! str_ends_with($file, '.json')) {
                continue;
            }

            try {
                $basename = basename($file);
                $fullPath = Storage::disk('local')->path($file);
                $content = file_get_contents($fullPath);
                $data = json_decode($content, true);

                if (! is_array($data)) {
                    continue;
                }

                $metadata = $data['metadata'] ?? [];

                $backups[] = [
                    'filename'   => $basename,
                    'size'       => Storage::disk('local')->size($file),
                    'size_label' => $this->formatBytes(Storage::disk('local')->size($file)),
                    'created_at' => $metadata['created_at'] ?? date('Y-m-d H:i:s', filemtime($fullPath)),
                    'created_by' => $metadata['created_by'] ?? '-',
                    'table_count' => $metadata['table_count'] ?? count($data['tables'] ?? []),
                    'tables'     => array_keys($data['tables'] ?? []),
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        usort($backups, fn ($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return $backups;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
