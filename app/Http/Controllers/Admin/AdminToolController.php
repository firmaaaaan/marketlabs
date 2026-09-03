<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\ToolImage;
use App\Support\ExcelExport;
use App\Support\ImportReadFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminToolController extends Controller
{
    public function index(Request $request)
    {
        $query = Tool::with('category')->latest();

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('code', 'like', "%{$escaped}%")
                    ->orWhere('brand', 'like', "%{$escaped}%");
            });
        }

        if ($request->has('status') && $request->query('status') !== '') {
            $query->where('is_active', $request->query('status') === 'active');
        }

        $tools = $query->paginate(15)->withQueryString();

        return view('admin.tools.index', compact('tools'));
    }

    public function create()
    {
        $categories = ToolCategory::orderBy('name')->get();
        $nextCode = $this->generateCode();

        return view('admin.tools.create', compact('categories', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTool($request);

        $tool = Tool::create([
            'code' => $this->generateCode(),
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'brand' => $validated['brand'] ?? null,
            'series' => $validated['series'] ?? null,
            'description' => $validated['description'] ?? null,
            'total_stock' => $validated['total_stock'],
            'available_stock' => $validated['total_stock'],
            'price_per_day' => $validated['price_per_day'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncImages($request, $tool);

        return redirect()->route('admin.tools.index')
            ->with('success', 'Alat berhasil ditambahkan.');
    }

    public function edit(Tool $tool)
    {
        $categories = ToolCategory::orderBy('name')->get();
        $tool->load('images');

        return view('admin.tools.edit', compact('tool', 'categories'));
    }

    public function update(Request $request, Tool $tool)
    {
        $validated = $this->validateTool($request, $tool);

        // Hitung selisih stok total untuk menyesuaikan stok tersedia.
        $diff = $validated['total_stock'] - $tool->total_stock;
        $newAvailable = $tool->available_stock + $diff;

        $tool->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'brand' => $validated['brand'] ?? null,
            'series' => $validated['series'] ?? null,
            'description' => $validated['description'] ?? null,
            'total_stock' => $validated['total_stock'],
            'available_stock' => max(0, $newAvailable),
            'price_per_day' => $validated['price_per_day'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncImages($request, $tool);

        return redirect()->route('admin.tools.index')
            ->with('success', 'Alat berhasil diperbarui.');
    }

    public function destroy(Tool $tool)
    {
        foreach ($tool->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        if ($tool->image) {
            Storage::disk('public')->delete($tool->image);
        }

        $tool->delete();

        return redirect()->route('admin.tools.index')
            ->with('success', 'Alat berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tools,id'],
        ]);

        $tools = Tool::whereIn('id', $validated['ids'])->get();

        foreach ($tools as $tool) {
            foreach ($tool->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            if ($tool->image) {
                Storage::disk('public')->delete($tool->image);
            }
            $tool->delete();
        }

        return redirect()->route('admin.tools.index')
            ->with('success', count($tools) . ' alat berhasil dihapus.');
    }

    /**
     * Export daftar alat ke Excel (.xlsx) mengikuti filter yang aktif.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Tool::with('category')->latest();

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('code', 'like', "%{$escaped}%")
                    ->orWhere('brand', 'like', "%{$escaped}%");
            });
        }

        if ($request->has('status') && $request->query('status') !== '') {
            $query->where('is_active', $request->query('status') === 'active');
        }

        $tools = $query->get();

        $rows = [[
            'Kode', 'Nama', 'Kategori', 'Merk', 'Seri', 'Deskripsi',
            'Total Stok', 'Stok Tersedia', 'Harga Sewa/Hari', 'Status Aktif',
        ]];

        foreach ($tools as $tool) {
            $rows[] = [
                $tool->code,
                $tool->name,
                $tool->category?->name ?? '',
                $tool->brand ?? '',
                $tool->series ?? '',
                $tool->description ?? '',
                $tool->total_stock,
                $tool->available_stock,
                $tool->price_per_day,
                $tool->is_active ? 'Aktif' : 'Nonaktif',
            ];
        }

        return ExcelExport::download('alat-'.now()->format('Ymd-His').'.xlsx', $rows);
    }

    /**
     * Template Excel (.xlsx) untuk import alat (bisa diunduh).
     */
    public function template(): StreamedResponse
    {
        $categories = ToolCategory::orderBy('name')->limit(3)->pluck('name')->implode(', ');

        return response()->streamDownload(function () use ($categories) {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Alat');

            $headers = ['Kode', 'Nama', 'Kategori', 'Merk', 'Seri', 'Deskripsi', 'Total Stok', 'Harga Sewa/Hari', 'Status Aktif'];
            $sheet->fromArray([$headers], null, 'A1');

            // Baris contoh (Kode boleh dikosongkan → dibuat otomatis).
            $sheet->fromArray([
                ['', 'Contoh Alat 1', $categories ?: 'Kategori A', 'Merk Contoh', 'S001', 'Deskripsi singkat alat.', '5', '50000', 'Aktif'],
                ['', 'Contoh Alat 2', $categories ?: 'Kategori B', '', '', '', '2', '75000', 'Nonaktif'],
            ], null, 'A2');

            // Gaya header.
            $sheet->getStyle('A1:I1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->freezePane('A2');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-import-alat.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import alat dari file Excel (.xlsx/.xls/.csv). Kode terisi → perbarui alat yang sama; kosong → buat baru.
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

        // Kolom yang dikenali di template.
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

            $result = $this->importRow($values, $usedCodes);

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

        if (! isset($indexMap['Nama'])) {
            return back()->with('error', 'Format file tidak dikenali. Gunakan template yang tersedia.');
        }

        $message = "Import selesai: {$created} alat ditambahkan, {$updated} alat diperbarui.";
        if ($skipped > 0) {
            $message .= " {$skipped} baris dilewati.";
            if (! empty($skipReasons)) {
                $message .= ' Alasan: '.implode('; ', $skipReasons);
            }
        }

        return back()->with('success', $message);
    }

    /**
     * Proses satu baris import: buat baru atau perbarui alat yang sudah ada.
     *
     * @param  array<string, string>  $values
     * @param  array<string, true>  $usedCodes
     */
    protected function importRow(array $values, array &$usedCodes): string
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
            // Perbarui alat yang sudah ada (sesuaikan stok tersedia).
            $diff = $data['total_stock'] - $tool->total_stock;
            $tool->update($data + ['available_stock' => max(0, $tool->available_stock + $diff)]);

            return 'updated';
        }

        // Kode baru: pakai kode dari file jika unik, selain itu otomatis.
        $newCode = $code !== '' && ! isset($usedCodes[$code]) && ! Tool::where('code', $code)->exists()
            ? $code
            : $this->generateCode();

        while (isset($usedCodes[$newCode])) {
            $newCode = $this->generateCode();
        }
        $usedCodes[$newCode] = true;

        Tool::create($data + [
            'code' => $newCode,
            'available_stock' => $totalStock,
        ]);

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

    protected function parseInt(string $value): int
    {
        $clean = preg_replace('/[^0-9]/', '', $value);

        return (int) $clean;
    }

    protected function parseActive(string $value): bool
    {
        return in_array(mb_strtolower($value), ['aktif', 'ya', '1', 'true', 'aktifkan'], true);
    }

    protected function nullIfEmpty(string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    /**
     * Buat kode alat otomatis dengan format AL-XXX (berurutan).
     */
    protected function generateCode(): string
    {
        // Ambil nomor urut terbesar dari semua kode AL-XXX agar tidak duplikat
        // meskipun ada alat yang pernah dihapus.
        $next = Tool::where('code', 'like', 'AL-%')
            ->get()
            ->map(fn ($t) => preg_match('/^AL-(\d+)$/', (string) $t->code, $m) ? (int) $m[1] : 0)
            ->max() + 1;

        return 'AL-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    protected function validateTool(Request $request, ?Tool $tool = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:tool_categories,id'],
            'brand' => ['nullable', 'string', 'max:100'],
            'series' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'total_stock' => ['required', 'integer', 'min:0'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);
    }

    /**
     * Sinkronisasi gambar alat: upload baru + hapus yang ditandai + urutkan.
     */
    protected function syncImages(Request $request, Tool $tool): void
    {
        // Upload gambar baru
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('tools', 'public');
                ToolImage::create([
                    'tool_id' => $tool->id,
                    'path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        // Migrate single image field ke tool_images (backward compat)
        if ($tool->image && $tool->images()->count() === 0) {
            ToolImage::create([
                'tool_id' => $tool->id,
                'path' => $tool->image,
                'sort_order' => 0,
            ]);
        }

        // Hapus gambar yang ditandai
        if ($request->has('delete_images')) {
            $deleteIds = $request->input('delete_images', []);
            $images = ToolImage::whereIn('id', $deleteIds)->where('tool_id', $tool->id)->get();
            foreach ($images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        // Update urutan jika ada
        if ($request->has('image_order')) {
            $order = $request->input('image_order', []);
            foreach ($order as $position => $imageId) {
                ToolImage::where('id', $imageId)->where('tool_id', $tool->id)
                    ->update(['sort_order' => $position]);
            }
        }
    }
}
