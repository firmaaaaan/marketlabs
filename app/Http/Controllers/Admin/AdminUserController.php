<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ExcelExport;
use App\Support\ImportReadFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('email', 'like', "%{$escaped}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(array_keys(User::roles()))],
            'nim_nip' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create($validated);
        $user->role = $validated['role'];
        $user->participant_code = User::generateParticipantCode();
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$validated['name']}' berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(array_keys(User::roles()))],
            'nim_nip' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // Jangan izinkan mengubah role admin lain.
        if ($user->isAdmin() && $validated['role'] !== User::ROLE_ADMIN && auth()->id() !== $user->id) {
            return back()->with('error', 'Tidak dapat mengubah role admin lain.');
        }

        // Jangan biarkan admin terakhir kehilangan role admin.
        if ($user->isAdmin() && $validated['role'] !== User::ROLE_ADMIN && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus role admin terakhir.');
        }

        $user->update($validated);
        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Tidak dapat menghapus akun admin lain.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' berhasil dihapus.");
    }

    /**
     * Export daftar user ke Excel (.xlsx).
     */
    public function export(): StreamedResponse
    {
        $users = User::latest()->get();

        $rows = [[
            'Nama', 'Email', 'NIM/NIK/NIP', 'Instansi', 'Role',
        ]];

        foreach ($users as $user) {
            $rows[] = [
                $user->name,
                $user->email,
                $user->nim_nip ?? '',
                $user->institution ?? '',
                User::roleLabel($user->role),
            ];
        }

        return ExcelExport::download('user-'.now()->format('Ymd-His').'.xlsx', $rows);
    }

    /**
     * Template Excel (.xlsx) untuk import user (bisa diunduh).
     */
    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template User');

            $headers = ['Nama', 'Email', 'NIM/NIK/NIP', 'Password', 'Role'];
            $sheet->fromArray([$headers], null, 'A1');

            // Baris contoh.
            $sheet->fromArray([
                ['Budi Santoso', 'budi@example.com', '1234-5678-90', 'rahasia123', 'User'],
                ['Siti Aminah', 'siti@example.com', '9876-5432-10', 'rahasia456', 'Laboran'],
                ['Dr. Ahmad', 'ahmad@unisa.ac.id', '0012-3456-78', 'rahasia789', 'User'],
            ], null, 'A2');

            // Gaya header.
            $sheet->getStyle('A1:E1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->freezePane('A2');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-import-user.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import user dari file Excel (.xlsx/.xls/.csv).
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:2048'],
        ]);

        $file = $validated['file'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['xlsx', 'xls', 'csv', 'txt'])) {
            return back()->with('error', 'File harus berformat Excel (.xlsx / .xls) atau CSV.');
        }

        try {
            $reader = IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $reader->setReadFilter(new ImportReadFilter);
            $spreadsheet = $reader->load($file->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'Tidak dapat membaca file. Pastikan file adalah Excel/CSV yang valid.');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $allRows = $sheet->toArray(null, true, true, true);
        $spreadsheet->disconnectWorksheets();

        $columns = ['Nama', 'Email', 'NIM/NIK/NIP', 'Password', 'Role'];

        $created = 0;
        $skipped = 0;
        $skipReasons = [];
        $indexMap = [];

        foreach ($allRows as $i => $row) {
            $row = array_values($row);

            if ($i === 1) {
                // Baris header (strip BOM bila ada).
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($row[0] ?? ''));
                $indexMap = $this->buildColumnMap($row, $columns);

                continue;
            }

            $values = $this->mapRow($row, $indexMap);

            // Lewati baris kosong.
            if (empty(implode('', $values))) {
                continue;
            }

            $result = $this->importRow($values);

            if ($result === 'created') {
                $created++;
            } else {
                $skipped++;
                if (count($skipReasons) < 5) {
                    $skipReasons[] = $result;
                }
            }
        }

        if (! isset($indexMap['Nama'])) {
            return back()->with('error', 'Format file tidak dikenali. Gunakan template yang tersedia.');
        }

        $message = "Import selesai: {$created} user berhasil ditambahkan.";
        if ($skipped > 0) {
            $message .= " {$skipped} baris dilewati.";
            if (! empty($skipReasons)) {
                $message .= ' Alasan: '.implode('; ', $skipReasons);
            }
        }

        return back()->with('success', $message);
    }

    /**
     * Proses satu baris import: buat user baru.
     *
     * @param  array<string, string>  $values
     */
    protected function importRow(array $values): string
    {
        $name = trim($values['Nama'] ?? '');
        if ($name === '') {
            return 'baris dengan Nama kosong';
        }

        $email = trim($values['Email'] ?? '');
        if ($email === '') {
            return "email kosong untuk '{$name}'";
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "format email tidak valid untuk '{$name}'";
        }

        if (User::where('email', $email)->exists()) {
            return "email '{$email}' sudah terdaftar";
        }

        $password = trim($values['Password'] ?? '');
        if ($password === '') {
            return "password kosong untuk '{$name}'";
        }

        $nimNip = trim($values['NIM/NIK/NIP'] ?? '');
        if ($nimNip !== '' && User::where('nim_nip', $nimNip)->exists()) {
            return "NIM/NIK/NIP '{$nimNip}' sudah terdaftar";
        }

        $roleValue = trim($values['Role'] ?? '');
        $roleMap = [
            'admin' => User::ROLE_ADMIN,
            'laboran' => User::ROLE_LABORAN,
            'user' => User::ROLE_USER,
        ];
        $roleKey = mb_strtolower($roleValue);
        $role = $roleMap[$roleKey] ?? User::ROLE_USER;

        $user = User::create([
            'name' => $name,
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

    /**
     * @param  array<int, string|null>  $header
     * @param  array<int, string>  $columns
     * @return array<string, int>
     */
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

    /**
     * @param  array<int, string|null>  $row
     * @param  array<string, int>  $indexMap
     * @return array<string, string>
     */
    protected function mapRow(array $row, array $indexMap): array
    {
        $values = [];

        foreach ($indexMap as $key => $index) {
            $values[$key] = trim((string) ($row[$index] ?? ''));
        }

        return $values;
    }
}
