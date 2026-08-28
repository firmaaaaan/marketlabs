<?php

namespace App\Http\Controllers;

use App\Models\SampleForm;
use App\Models\SampleTest;
use App\Models\SampleTestItem;
use App\Models\SampleType;
use App\Models\SampleUnit;
use App\Models\TestParameter;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SampleTestController extends Controller
{
    public function index()
    {
        $tests = SampleTest::with(['items.parameter.unit', 'items.sampleForm', 'items.sampleType'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('sample-tests.index', compact('tests'));
    }

    /**
     * Katalog layanan pengujian (publik untuk user login).
     */
    public function catalog(Request $request)
    {
        $query = TestParameter::with('unit')->active()->latest();

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where('name', 'like', "%{$escaped}%");
        }

        if ($unitId = $request->query('unit_id')) {
            $query->where('unit_id', $unitId);
        }

        $parameters = $query->paginate(12)->withQueryString();
        $units = SampleUnit::active()->orderBy('name')->get();

        return view('sample-tests.catalog', compact('parameters', 'units'));
    }

    /**
     * Form checkout: tiap layanan (parameter) dari keranjang menampilkan daftar sampelnya sendiri.
     * Satu layanan boleh dipakai oleh lebih dari satu sampel.
     */
    public function checkout()
    {
        $cart = session('test_cart', []);

        if (empty($cart)) {
            return redirect()->route('sample-tests.catalog')
                ->with('info', 'Keranjang pengujian masih kosong. Pilih layanan dari katalog terlebih dahulu.');
        }

        $services = [];

        foreach ($cart as $parameterId => $_) {
            $parameter = TestParameter::with('unit')->find($parameterId);

            if (! $parameter || ! $parameter->is_active) {
                continue;
            }

            $services[] = $parameter;
        }

        if (empty($services)) {
            return redirect()->route('sample-tests.catalog')
                ->with('info', 'Keranjang pengujian kosong atau berisi layanan yang sudah nonaktif.');
        }

        $forms = SampleForm::active()->orderBy('name')->get();
        $types = SampleType::active()->orderBy('name')->get();

        return view('sample-tests.checkout', compact('services', 'forms', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $cart = session('test_cart', []);

        if (empty($cart)) {
            return back()->withErrors(['services' => 'Keranjang pengujian kosong.'])->withInput();
        }

        // Parameter dari keranjang yang valid & aktif.
        $cartParameters = TestParameter::active()->whereIn('id', array_keys($cart))->get()->keyBy('id');

        if ($cartParameters->isEmpty()) {
            return back()->withErrors(['services' => 'Tidak ada layanan pengujian valid di keranjang.'])->withInput();
        }

        $test = DB::transaction(function () use ($validated, $cartParameters) {
            $test = SampleTest::create([
                'user_id' => auth()->id(),
                'code' => $this->generateCode(),
                'notes' => trim((string) ($validated['notes'] ?? '')) !== '' ? trim($validated['notes']) : null,
                'delivery_method' => $validated['delivery_method'],
                'status' => SampleTest::STATUS_PENDING,
                'total_cost' => 0,
                'payment_status' => SampleTest::PAYMENT_UNPAID,
            ]);

            foreach ($validated['services'] as $parameterId => $rows) {
                $parameter = $cartParameters->get($parameterId);

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

            $test->load('items');
            $test->recalculateTotalCost();

            return $test;
        });

        session()->forget('test_cart');

        // Beri tahu admin bahwa ada pengujian baru menunggu persetujuan.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                'Pengujian Sampel Baru',
                "Pengujian sampel {$test->code} diajukan oleh ".auth()->user()->name.' dan menunggu persetujuan.',
                route('admin.sample-tests.show', $test),
                notifyViaEmail: true,
            ));
        }

        return redirect()->route('sample-tests.show', $test)
            ->with('success', "Pengujian sampel {$test->code} berhasil diajukan dan menunggu persetujuan.");
    }

    public function parameter(TestParameter $parameter)
    {
        abort_unless($parameter->is_active, 404);

        $parameter->load('unit');

        $related = TestParameter::active()->with('unit')
            ->where('unit_id', $parameter->unit_id)
            ->where('id', '!=', $parameter->id)
            ->limit(3)
            ->get();

        return view('sample-tests.parameter', compact('parameter', 'related'));
    }

    public function show(SampleTest $test)
    {
        // Pemilik pengujian atau laboran yang ditugaskan bisa melihat detail.
        abort_unless($test->user_id === auth()->id() || $test->laboran_id === auth()->id(), 403);

        $test->load(['items.parameter.unit', 'items.sampleForm', 'items.sampleType', 'user']);

        return view('sample-tests.show', compact('test'));
    }

    public function cancel(SampleTest $test)
    {
        abort_unless($test->user_id === auth()->id(), 403);
        abort_unless(in_array($test->status, [
            SampleTest::STATUS_PENDING,
            SampleTest::STATUS_APPROVED,
        ]), 403);

        $test->update(['status' => SampleTest::STATUS_CANCELLED]);

        return back()->with('success', 'Pengujian sampel dibatalkan.');
    }

    public function invoice(SampleTest $test)
    {
        abort_unless($test->user_id === auth()->id(), 403);

        $test->load(['items.parameter.unit', 'items.sampleForm', 'items.sampleType', 'user']);

        return view('sample-tests.invoice', compact('test'));
    }

    protected function generateCode(): string
    {
        $prefix = 'UJI-'.date('Ymd');

        do {
            $code = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (SampleTest::where('code', $code)->exists());

        return $code;
    }
}
