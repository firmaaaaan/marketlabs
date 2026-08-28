<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SampleForm;
use App\Models\SampleTest;
use App\Models\SampleTestItem;
use App\Models\SampleType;
use App\Models\TestParameter;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminSampleTestController extends Controller
{
    public function index(Request $request)
    {
        $query = SampleTest::with(['user', 'items.parameter.unit', 'items.sampleForm', 'items.sampleType'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($payment = $request->query('payment')) {
            $query->where('payment_status', $payment);
        }

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('code', 'like', "%{$escaped}%")
                    ->orWhereHas('items', fn ($i) => $i->where('sample_name', 'like', "%{$escaped}%"))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$escaped}%"));
            });
        }

        $tests = $query->paginate(15)->withQueryString();

        return view('admin.sample-tests.index', compact('tests'));
    }

    public function show(SampleTest $test)
    {
        $test->load(['user', 'items.parameter.unit', 'items.sampleForm', 'items.sampleType', 'laboran']);

        $laborans = User::laboran()->orderBy('name')->get();

        return view('admin.sample-tests.show', compact('test', 'laborans'));
    }

    public function updateAssignment(Request $request, SampleTest $test)
    {
        $validated = $request->validate([
            'laboran_id' => ['nullable', 'string', Rule::exists('users', 'id')->where('role', User::ROLE_LABORAN)],
        ]);

        $test->update([
            'laboran_id' => $validated['laboran_id'] ?? null,
        ]);

        // Beri tahu laboran bila pengujian ditugaskan kepadanya.
        if (! empty($validated['laboran_id'])) {
            User::find($validated['laboran_id'])?->notify(new BorrowingNotification(
                'Pengujian Ditugaskan',
                "Pengujian sampel {$test->code} ditugaskan kepada Anda oleh admin.",
                route('sample-tests.show', $test),
                notifyViaEmail: true,
            ));
        }

        return back()->with('success', "Penugasan laboran untuk {$test->code} berhasil disimpan.");
    }

    public function create()
    {
        $users = User::where('role', User::ROLE_USER)->orderBy('name')->get();
        $parameters = TestParameter::with('unit')->active()->orderBy('name')->get();
        $forms = SampleForm::active()->orderBy('name')->get();
        $types = SampleType::active()->orderBy('name')->get();

        return view('admin.sample-tests.create', compact('users', 'parameters', 'forms', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateServices($request);

        $test = DB::transaction(function () use ($validated) {
            $test = SampleTest::create([
                'user_id' => $validated['user_id'],
                'code' => $this->generateCode(),
                'notes' => trim((string) ($validated['notes'] ?? '')) !== '' ? trim($validated['notes']) : null,
                'delivery_method' => $validated['delivery_method'],
                'status' => SampleTest::STATUS_PENDING,
                'total_cost' => 0,
                'payment_status' => SampleTest::PAYMENT_UNPAID,
            ]);

            $this->saveItems($test, $validated['services']);

            $test->load('items');
            $test->recalculateTotalCost();

            return $test;
        });

        return redirect()->route('admin.sample-tests.show', $test)
            ->with('success', "Pengujian sampel {$test->code} berhasil dibuat.");
    }

    public function edit(SampleTest $test)
    {
        $test->load(['user', 'items.parameter', 'items.sampleForm', 'items.sampleType']);

        $users = User::where('role', User::ROLE_USER)->orderBy('name')->get();
        $parameters = TestParameter::with('unit')->active()->orderBy('name')->get();
        $forms = SampleForm::active()->orderBy('name')->get();
        $types = SampleType::active()->orderBy('name')->get();

        return view('admin.sample-tests.edit', compact('test', 'users', 'parameters', 'forms', 'types'));
    }

    public function update(Request $request, SampleTest $test)
    {
        $validated = $this->validateServices($request);

        DB::transaction(function () use ($validated, $test) {
            $test->update([
                'user_id' => $validated['user_id'],
                'notes' => trim((string) ($validated['notes'] ?? '')) !== '' ? trim($validated['notes']) : null,
                'delivery_method' => $validated['delivery_method'],
            ]);

            $test->items()->delete();
            $this->saveItems($test, $validated['services']);

            $test->load('items');
            $test->recalculateTotalCost();
        });

        return redirect()->route('admin.sample-tests.show', $test)
            ->with('success', "Pengujian sampel {$test->code} berhasil diperbarui.");
    }

    protected function validateServices(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'delivery_method' => ['required', Rule::in([SampleTest::DELIVERY_DIRECT, SampleTest::DELIVERY_PACKAGED])],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['required', 'array', 'min:1'],
            'services.*.*.sample_name' => ['required', 'string', 'max:255'],
            'services.*.*.sample_description' => ['nullable', 'string', 'max:2000'],
            'services.*.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'services.*.*.sample_form_id' => ['nullable', 'exists:sample_forms,id'],
            'services.*.*.sample_type_id' => ['nullable', 'exists:sample_types,id'],
        ]);
    }

    /**
     * Simpan daftar sampel per layanan (rate snapshot dari parameter aktif).
     * Format input: services[parameterId][] = baris sampel.
     */
    protected function saveItems(SampleTest $test, array $services): void
    {
        foreach ($services as $parameterId => $rows) {
            $parameter = TestParameter::active()->find($parameterId);

            if (! $parameter || empty($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                SampleTestItem::create([
                    'sample_test_id' => $test->id,
                    'parameter_id' => $parameter->id,
                    'sample_name' => $row['sample_name'],
                    'sample_description' => trim((string) ($row['sample_description'] ?? '')) !== '' ? trim($row['sample_description']) : null,
                    'quantity' => $row['quantity'],
                    'sample_form_id' => ! empty($row['sample_form_id']) ? $row['sample_form_id'] : null,
                    'sample_type_id' => ! empty($row['sample_type_id']) ? $row['sample_type_id'] : null,
                    'rate' => $parameter->rate,
                ]);
            }
        }
    }

    public function destroy(SampleTest $test)
    {
        $test->delete();

        return redirect()->route('admin.sample-tests.index')
            ->with('success', "Pengujian sampel {$test->code} berhasil dihapus.");
    }

    public function updateStatus(Request $request, SampleTest $test)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                SampleTest::STATUS_APPROVED,
                SampleTest::STATUS_RECEIVED,
                SampleTest::STATUS_TESTING,
                SampleTest::STATUS_DONE,
                SampleTest::STATUS_REJECTED,
            ])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data = ['status' => $validated['status']];

        $now = now();

        switch ($validated['status']) {
            case SampleTest::STATUS_APPROVED:
                $data['processed_at'] = $now;
                $data['approved_at'] = $now;
                break;
            case SampleTest::STATUS_RECEIVED:
                $data['processed_at'] = $now;
                $data['received_at'] = $now;
                break;
            case SampleTest::STATUS_TESTING:
                $data['processed_at'] = $now;
                $data['tested_at'] = $now;
                break;
            case SampleTest::STATUS_DONE:
                $data['processed_at'] = $now;
                $data['done_at'] = $now;
                break;
            case SampleTest::STATUS_REJECTED:
                $data['processed_at'] = $now;
                $data['rejected_at'] = $now;
                break;
        }

        if (! empty($validated['admin_notes'])) {
            $data['result_notes'] = $validated['admin_notes'];
        }

        $test->update($data);

        $title = match ($validated['status']) {
            SampleTest::STATUS_APPROVED => 'Pengujian Disetujui',
            SampleTest::STATUS_RECEIVED => 'Sampel Diterima',
            SampleTest::STATUS_TESTING => 'Pengujian Sedang Berlangsung',
            SampleTest::STATUS_DONE => 'Pengujian Selesai',
            SampleTest::STATUS_REJECTED => 'Pengujian Ditolak',
            default => 'Status Pengujian Diperbarui',
        };

        $test->user->notify(new BorrowingNotification(
            $title,
            "Pengujian sampel {$test->code} telah ditandai '{$test->status_label}'. Silakan cek detail pengujian.",
            route('sample-tests.show', $test),
            notifyViaEmail: true,
        ));

        return back()->with('success', 'Status pengujian '.$test->code.' diperbarui menjadi '.$test->status_label.'.');
    }

    public function updateResult(Request $request, SampleTest $test)
    {
        $validated = $request->validate([
            'result' => ['required', 'string', 'max:255'],
            'result_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $test->update([
            'result' => $validated['result'],
            'result_notes' => trim((string) ($validated['result_notes'] ?? '')) !== '' ? trim($validated['result_notes']) : null,
        ]);

        $test->user->notify(new BorrowingNotification(
            'Hasil Pengujian Tersedia',
            "Hasil pengujian sampel {$test->code} telah diinput: {$validated['result']}. Silakan cek detail pengujian.",
            route('sample-tests.show', $test),
            notifyViaEmail: true,
        ));

        return back()->with('success', 'Hasil pengujian '.$test->code.' berhasil disimpan.');
    }

    /**
     * Upload dokumen hasil pengujian — khusus admin.
     */
    public function uploadResultFile(Request $request, SampleTest $test)
    {
        $validated = $request->validate([
            'result_file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('result_file');
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getMimeType());

        $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $allowedMimes = [
            'application/pdf', 'application/x-pdf', 'text/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 'image/png',
        ];

        $isAllowedExt = in_array($ext, $allowedExts, true);
        $isAllowedMime = in_array($mime, $allowedMimes, true);

        // Both extension AND MIME type must be in allowed lists to prevent bypass.
        if (! $isAllowedExt || ! $isAllowedMime) {
            // Di Windows banyak PDF hasil scan terdeteksi sebagai application/octet-stream.
            // Terima hanya bila ekstensi aman DAN konten hasil sniff bukan skrip/HTML.
            if (in_array($mime, ['application/octet-stream', 'binary/octet-stream'], true)) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $real = strtolower((string) $finfo->file($file->getRealPath()));

                if (! in_array($real, $allowedMimes, true) || str_starts_with($real, 'text/')) {
                    return back()->withErrors(['result_file' => 'Dokumen harus berupa PDF, DOC/DOCX, atau gambar JPG/PNG (maks 10 MB).'])->withInput();
                }
            } else {
                return back()->withErrors(['result_file' => 'Dokumen harus berupa PDF, DOC/DOCX, atau gambar JPG/PNG (maks 10 MB).'])->withInput();
            }
        }

        $userName = \Illuminate\Support\Str::slug($test->user->name);
        $ext = strtolower($ext);
        $path = $request->file('result_file')->storeAs('test-results', 'hasil-'.$test->code.'-'.$userName.'.'.$ext);

        // Hapus file lama bila diganti.
        if ($test->result_file) {
            if (Storage::disk('local')->exists($test->result_file)) {
                Storage::disk('local')->delete($test->result_file);
            } elseif (Storage::disk('public')->exists($test->result_file)) {
                Storage::disk('public')->delete($test->result_file);
            }
        }

        $test->update(['result_file' => $path]);

        $test->user->notify(new BorrowingNotification(
            'Dokumen Hasil Diunggah',
            "Dokumen hasil pengujian sampel {$test->code} telah diunggah oleh admin.",
            route('sample-tests.show', $test),
            notifyViaEmail: true,
        ));

        return back()->with('success', 'Dokumen hasil pengujian '.$test->code.' berhasil diunggah.');
    }

    public function updatePayment(Request $request, SampleTest $test)
    {
        $validated = $request->validate([
            'payment_status' => ['required', Rule::in([SampleTest::PAYMENT_UNPAID, SampleTest::PAYMENT_PAID])],
        ]);

        $data = ['payment_status' => $validated['payment_status']];

        if ($validated['payment_status'] === SampleTest::PAYMENT_PAID && ! $test->invoice_number) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        $test->update($data);

        $paid = $validated['payment_status'] === SampleTest::PAYMENT_PAID;

        $test->user->notify(new BorrowingNotification(
            $paid ? 'Pembayaran Lunas' : 'Pembayaran Belum Dibayar',
            $paid
                ? "Pembayaran pengujian sampel {$test->code} telah ditandai lunas. Silakan cek invoice pada detail pengujian."
                : "Status pembayaran pengujian sampel {$test->code} diubah menjadi belum dibayar.",
            route('sample-tests.show', $test),
            notifyViaEmail: true,
        ));

        return back()->with('success', 'Status pembayaran '.$test->code.' diperbarui menjadi '.SampleTest::paymentStatusLabel($validated['payment_status']).'.');
    }

    public function invoice(SampleTest $test)
    {
        $test->load(['user', 'items.parameter.unit', 'items.sampleForm', 'items.sampleType']);

        return view('sample-tests.invoice', compact('test'));
    }

    /**
     * Halaman cetak label sampel.
     */
    public function print(SampleTest $test)
    {
        $test->load(['user', 'items.parameter.unit', 'items.sampleForm', 'items.sampleType']);

        $backUrl = route('admin.sample-tests.show', $test);

        return view('sample-tests.print', compact('test', 'backUrl'));
    }

    protected function generateCode(): string
    {
        $prefix = 'UJI-'.date('Ymd');

        do {
            $code = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (SampleTest::where('code', $code)->exists());

        return $code;
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-UJI-'.date('Ymd');

        do {
            $number = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (SampleTest::where('invoice_number', $number)->exists());

        return $number;
    }
}
