<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DatabaseBackupJob;
use App\Jobs\DatabaseRestoreJob;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'tables' => 'nullable|array',
            'tables.*' => 'string',
        ]);

        $selectedTables = $request->input('tables');

        DatabaseBackupJob::dispatch($selectedTables, auth()->id());

        return back()->with('success', 'Backup database sedang diproses di queue. Anda akan mendapat notifikasi setelah selesai.');
    }

    public function download(string $filename)
    {
        $path = $this->backupDir.'/'.$filename;

        if (! Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        $fullPath = Storage::disk('local')->path($path);

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function destroy(string $filename)
    {
        $path = $this->backupDir.'/'.$filename;

        if (! Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        Storage::disk('local')->delete($path);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'role' => auth()->user()->role,
            'action' => 'backup_delete',
            'description' => 'Backup dihapus: '.$filename,
            'properties' => ['filename' => $filename],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Backup berhasil dihapus.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:10240',
            'confirm' => 'required|accepted',
        ]);

        $file = $request->file('backup_file');
        $path = $file->store('backups/temp');

        DatabaseRestoreJob::dispatch($path, auth()->id());

        return back()->with('success', 'Restore database sedang diproses di queue. Anda akan mendapat notifikasi setelah selesai.');
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
                    'filename' => $basename,
                    'size' => Storage::disk('local')->size($file),
                    'size_label' => $this->formatBytes(Storage::disk('local')->size($file)),
                    'created_at' => $metadata['created_at'] ?? date('Y-m-d H:i:s', filemtime($fullPath)),
                    'created_by' => $metadata['created_by'] ?? '-',
                    'table_count' => $metadata['table_count'] ?? count($data['tables'] ?? []),
                    'tables' => array_keys($data['tables'] ?? []),
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

        return round($bytes, $precision).' '.$units[$pow];
    }
}
