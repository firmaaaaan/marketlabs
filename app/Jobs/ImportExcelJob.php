<?php

namespace App\Jobs;

use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\User;
use App\Notifications\EventNotification;
use App\Support\ImportReadFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public string $filePath,
        public string $type,
        public string $userId,
    ) {}

    public function handle(): void
    {
        $result = match ($this->type) {
            'tool' => $this->importTools(),
            'user' => $this->importUsers(),
            default => throw new \InvalidArgumentException("Unknown import type: {$this->type}"),
        };

        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new EventNotification(
                'Import Selesai',
                $result['message'],
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
                'Import Gagal',
                "Import {$this->type} gagal: {$exception->getMessage()}",
                notifyViaEmail: false,
            ));
        }

        @unlink($this->filePath);
    }

    protected function importTools(): array
    {
        $spreadsheet = $this->loadSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $allRows = $sheet->toArray(null, true, true, true);
        $spreadsheet->disconnectWorksheets();

        $columns = ['Kode', 'Nama', 'Kategori', 'Merk', 'Seri', 'Deskripsi', 'Total Stok', 'Harga Sewa/Hari', 'Status Aktif'];

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $skipReasons = [];
        $usedCodes = [];
        $indexMap = [];

        foreach ($allRows as $i => $row) {
            $row = array_values($row);

            if ($i === 1) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($row[0] ?? ''));
                $indexMap = $this->buildColumnMap($row, $columns);

                continue;
            }

            $values = $this->mapRow($row, $indexMap);

            if (empty(implode('', $values))) {
                continue;
            }

            $result = $this->importToolRow($values, $usedCodes);

            if ($result === 'created') {
                $created++;
            } elseif ($result === 'updated') {
                $updated++;
            } else {
                $skipped++;
                if (count($skipReasons) < 5) {
                    $skipReasons[] = $result;
                }
            }
        }

        $message = "Import selesai: {$created} alat ditambahkan, {$updated} alat diperbarui.";
        if ($skipped > 0) {
            $message .= " {$skipped} baris dilewati.";
            if (! empty($skipReasons)) {
                $message .= ' Alasan: '.implode('; ', $skipReasons);
            }
        }

        return ['message' => $message];
    }

    protected function importUsers(): array
    {
        $spreadsheet = $this->loadSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $allRows = $sheet->toArray(null, true, true, true);
        $spreadsheet->disconnectWorksheets();

        $columns = ['Nama', 'Username', 'NIM/NIK/NIP', 'Password', 'Role'];

        $created = 0;
        $skipped = 0;
        $skipReasons = [];
        $indexMap = [];

        foreach ($allRows as $i => $row) {
            $row = array_values($row);

            if ($i === 1) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($row[0] ?? ''));
                $indexMap = $this->buildColumnMap($row, $columns);

                continue;
            }

            $values = $this->mapRow($row, $indexMap);

            if (empty(implode('', $values))) {
                continue;
            }

            $result = $this->importUserRow($values);

            if ($result === 'created') {
                $created++;
            } else {
                $skipped++;
                if (count($skipReasons) < 5) {
                    $skipReasons[] = $result;
                }
            }
        }

        $message = "Import selesai: {$created} user berhasil ditambahkan.";
        if ($skipped > 0) {
            $message .= " {$skipped} baris dilewati.";
            if (! empty($skipReasons)) {
                $message .= ' Alasan: '.implode('; ', $skipReasons);
            }
        }

        return ['message' => $message];
    }

    protected function importToolRow(array $values, array &$usedCodes): string
    {
        $name = trim($values['Nama'] ?? '');
        if ($name === '') {
            return 'baris dengan Nama kosong';
        }

        $categoryName = trim($values['Kategori'] ?? '');
        if ($categoryName === '') {
            return "kategori kosong untuk '{$name}'";
        }

        $totalStock = $this->parseInt($values['Total Stok'] ?? '0');
        $price = $this->parseInt($values['Harga Sewa/Hari'] ?? '0');

        if ($totalStock < 0) {
            return "stok tidak valid untuk '{$name}'";
        }
        if ($price < 0) {
            return "harga tidak valid untuk '{$name}'";
        }

        $category = ToolCategory::whereRaw('LOWER(name) = ?', [mb_strtolower($categoryName)])->first();
        if (! $category) {
            $category = ToolCategory::create(['name' => $categoryName]);
        }

        $code = trim($values['Kode'] ?? '');
        $tool = $code !== '' ? Tool::where('code', $code)->first() : null;

        $data = [
            'name' => $name,
            'category_id' => $category->id,
            'brand' => $this->nullIfEmpty($values['Merk'] ?? ''),
            'series' => $this->nullIfEmpty($values['Seri'] ?? ''),
            'description' => $this->nullIfEmpty($values['Deskripsi'] ?? ''),
            'total_stock' => $totalStock,
            'price_per_day' => $price,
            'is_active' => $this->parseActive($values['Status Aktif'] ?? ''),
        ];

        if ($tool) {
            $diff = $data['total_stock'] - $tool->total_stock;
            $tool->update($data + ['available_stock' => max(0, $tool->available_stock + $diff)]);

            return 'updated';
        }

        $newCode = $code !== '' && ! isset($usedCodes[$code]) && ! Tool::where('code', $code)->exists()
            ? $code
            : $this->generateToolCode();

        while (isset($usedCodes[$newCode])) {
            $newCode = $this->generateToolCode();
        }
        $usedCodes[$newCode] = true;

        Tool::create($data + [
            'code' => $newCode,
            'available_stock' => $totalStock,
        ]);

        return 'created';
    }

    protected function importUserRow(array $values): string
    {
        $name = trim($values['Nama'] ?? '');
        if ($name === '') {
            return 'baris dengan Nama kosong';
        }

        $username = trim($values['Username'] ?? '');
        if ($username === '') {
            return "username kosong untuk '{$name}'";
        }

        if (User::where('username', $username)->exists()) {
            return "username '{$username}' sudah terdaftar";
        }

        $email = trim($values['Email'] ?? '');
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "format email tidak valid untuk '{$name}'";
        }

        if ($email !== '' && User::where('email', $email)->exists()) {
            return "email '{$email}' sudah terdaftar";
        }

        if ($email === '') {
            $email = $username.'@pending.local';
        }

        $password = trim($values['Password'] ?? '');
        if ($password === '') {
            return "password kosong untuk '{$name}'";
        }

        $nimNip = trim($values['NIM/NIK/NIP'] ?? '');
        if ($nimNip !== '' && User::where('nim_nip', $nimNip)->exists()) {
            return "NIM/NIK/NIP '{$nimNip}' sudah terdaftar";
        }

        $roleMap = [
            'admin' => User::ROLE_ADMIN,
            'laboran' => User::ROLE_LABORAN,
            'user' => User::ROLE_USER,
        ];
        $role = $roleMap[mb_strtolower(trim($values['Role'] ?? ''))] ?? User::ROLE_USER;

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'nim_nip' => $nimNip !== '' ? $nimNip : null,
            'participant_code' => User::generateParticipantCode(),
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        $user->role = $role;
        $user->save();

        return 'created';
    }

    protected function loadSpreadsheet()
    {
        $reader = IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $reader->setReadFilter(new ImportReadFilter);

        return $reader->load($this->filePath);
    }

    protected function buildColumnMap(array $header, array $columns): array
    {
        $map = [];
        foreach ($header as $i => $cell) {
            $key = trim((string) $cell);
            if (in_array($key, $columns, true)) {
                $map[$key] = $i;
            }
        }

        return $map;
    }

    protected function mapRow(array $row, array $indexMap): array
    {
        $values = [];
        foreach ($indexMap as $key => $index) {
            $values[$key] = trim((string) ($row[$index] ?? ''));
        }

        return $values;
    }

    protected function parseInt(string $value): int
    {
        return (int) preg_replace('/[^0-9]/', '', $value);
    }

    protected function parseActive(string $value): bool
    {
        return in_array(mb_strtolower($value), ['aktif', 'ya', '1', 'true', 'aktifkan'], true);
    }

    protected function nullIfEmpty(string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    protected function generateToolCode(): string
    {
        $next = Tool::where('code', 'like', 'AL-%')
            ->get()
            ->map(fn ($t) => preg_match('/^AL-(\d+)$/', (string) $t->code, $m) ? (int) $m[1] : 0)
            ->max() + 1;

        return 'AL-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
