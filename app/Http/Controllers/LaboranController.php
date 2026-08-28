<?php

namespace App\Http\Controllers;

use App\Models\HealthCheckup;
use App\Models\ResearchProposal;
use App\Models\SampleTest;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use App\Support\ServiceSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaboranController extends Controller
{
    /**
     * Dashboard laboran: ringkasan statistik serta daftar riset & pengujian
     * yang ditugaskan ke laboran yang sedang login (tabel + filter + pencarian).
     */
    public function index(Request $request)
    {
        $laboran = auth()->user();

        // Statistik dihitung dari seluruh data (tidak terfilter).
        $allRisets = ResearchProposal::where('laboran_id', $laboran->id)->get();
        $allTests = SampleTest::where('laboran_id', $laboran->id)->get();
        $allCheckups = HealthCheckup::where('examiner_id', $laboran->id)->get();

        $totalRisets = $allRisets->count();
        $totalTests = $allTests->count();

        $activeRisets = $allRisets->filter(fn ($r) => in_array($r->status, [
            ResearchProposal::STATUS_APPROVED,
            ResearchProposal::STATUS_ONGOING,
        ]))->count();
        $pendingRisets = $allRisets->filter(fn ($r) => $r->status === ResearchProposal::STATUS_PENDING)->count();
        $doneRisets = $allRisets->filter(fn ($r) => $r->status === ResearchProposal::STATUS_DONE)->count();

        $activeTests = $allTests->filter(fn ($t) => in_array($t->status, [
            SampleTest::STATUS_APPROVED,
            SampleTest::STATUS_RECEIVED,
            SampleTest::STATUS_TESTING,
        ]))->count();
        $pendingTests = $allTests->filter(fn ($t) => $t->status === SampleTest::STATUS_PENDING)->count();
        $doneTests = $allTests->filter(fn ($t) => $t->status === SampleTest::STATUS_DONE)->count();
        $unpaidTests = $allTests->filter(fn ($t) => $t->payment_status === SampleTest::PAYMENT_UNPAID
            && ! in_array($t->status, [SampleTest::STATUS_REJECTED, SampleTest::STATUS_CANCELLED]))->count();

        $totalCheckups = $allCheckups->count();
        $activeCheckups = $allCheckups->filter(fn ($c) => in_array($c->status, [
            HealthCheckup::STATUS_PENDING,
            HealthCheckup::STATUS_APPROVED,
        ]))->count();
        $pendingCheckups = $allCheckups->filter(fn ($c) => $c->status === HealthCheckup::STATUS_PENDING)->count();
        $doneCheckups = $allCheckups->filter(fn ($c) => $c->status === HealthCheckup::STATUS_DONE)->count();
        $unpaidCheckups = $allCheckups->filter(fn ($c) => $c->payment_status === HealthCheckup::PAYMENT_UNPAID
            && ! in_array($c->status, [HealthCheckup::STATUS_REJECTED, HealthCheckup::STATUS_CANCELLED]))->count();

        // Daftar riset: filter status + pencarian.
        $risetQuery = ResearchProposal::with(['user', 'laboratorium'])
            ->where('laboran_id', $laboran->id);

        if ($status = $request->query('riset_status')) {
            $risetQuery->where('status', $status);
        }

        if ($search = trim((string) $request->query('riset_search'))) {
            $escaped = addcslashes($search, '%_');
            $risetQuery->where(function ($q) use ($escaped) {
                $q->where('code', 'like', "%{$escaped}%")
                    ->orWhere('title', 'like', "%{$escaped}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$escaped}%"));
            });
        }

        $risets = $risetQuery->latest()->paginate(10, ['*'], 'riset_page')->withQueryString();

        // Daftar pengujian: filter status + pencarian.
        $testQuery = SampleTest::with(['user', 'items.parameter.unit'])
            ->where('laboran_id', $laboran->id);

        if ($status = $request->query('test_status')) {
            $testQuery->where('status', $status);
        }

        if ($search = trim((string) $request->query('test_search'))) {
            $escaped = addcslashes($search, '%_');
            $testQuery->where(function ($q) use ($escaped) {
                $q->where('code', 'like', "%{$escaped}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$escaped}%"))
                    ->orWhereHas('items.parameter', fn ($p) => $p->where('name', 'like', "%{$escaped}%"));
            });
        }

        $tests = $testQuery->latest()->paginate(10, ['*'], 'test_page')->withQueryString();

        // Daftar pemeriksaan kesehatan: filter status (antrian yang ditugaskan ke laboran ini).
        $checkupQuery = HealthCheckup::with(['user', 'type'])
            ->where('examiner_id', $laboran->id);

        if ($status = $request->query('checkup_status')) {
            $checkupQuery->where('status', $status);
        }

        $checkups = $checkupQuery->latest('booking_date')->paginate(10, ['*'], 'checkup_page')->withQueryString();

        // Posisi antrian per tanggal yang muncul di halaman ini.
        $queues = [];
        foreach ($checkups->groupBy(fn ($c) => $c->booking_date->toDateString()) as $date => $items) {
            $queues[$date] = ServiceSchedule::queueInfo($date);
        }

        $schedule = ServiceSchedule::summary();

        $risetStatuses = [
            ResearchProposal::STATUS_PENDING => 'Menunggu Persetujuan',
            ResearchProposal::STATUS_APPROVED => 'Disetujui',
            ResearchProposal::STATUS_ONGOING => 'Berjalan',
            ResearchProposal::STATUS_DONE => 'Selesai',
            ResearchProposal::STATUS_REJECTED => 'Ditolak',
            ResearchProposal::STATUS_CANCELLED => 'Dibatalkan',
        ];

        $testStatuses = [
            SampleTest::STATUS_PENDING => 'Menunggu Persetujuan',
            SampleTest::STATUS_APPROVED => 'Disetujui',
            SampleTest::STATUS_RECEIVED => 'Sampel Diterima',
            SampleTest::STATUS_TESTING => 'Sedang Diuji',
            SampleTest::STATUS_DONE => 'Selesai',
            SampleTest::STATUS_REJECTED => 'Ditolak',
            SampleTest::STATUS_CANCELLED => 'Dibatalkan',
        ];

        $checkupStatuses = [
            HealthCheckup::STATUS_PENDING => 'Menunggu',
            HealthCheckup::STATUS_APPROVED => 'Terjadwal',
            HealthCheckup::STATUS_DONE => 'Selesai',
            HealthCheckup::STATUS_REJECTED => 'Ditolak',
            HealthCheckup::STATUS_CANCELLED => 'Dibatalkan',
        ];

        return view('laboran.index', compact(
            'laboran', 'risets', 'tests', 'checkups', 'queues', 'schedule',
            'totalRisets', 'totalTests',
            'activeRisets', 'pendingRisets', 'doneRisets',
            'activeTests', 'pendingTests', 'doneTests', 'unpaidTests',
            'totalCheckups', 'activeCheckups', 'pendingCheckups', 'doneCheckups', 'unpaidCheckups',
            'risetStatuses', 'testStatuses', 'checkupStatuses',
        ));
    }

    /**
     * Ubah status pengujian yang ditugaskan ke laboran ini.
     */
    public function updateStatus(Request $request, SampleTest $test)
    {
        abort_unless($test->laboran_id === auth()->id(), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                SampleTest::STATUS_APPROVED,
                SampleTest::STATUS_RECEIVED,
                SampleTest::STATUS_TESTING,
                SampleTest::STATUS_DONE,
                SampleTest::STATUS_REJECTED,
            ])],
        ]);

        $data = ['status' => $validated['status'], 'processed_at' => now()];

        switch ($validated['status']) {
            case SampleTest::STATUS_APPROVED:
                $data['approved_at'] = now();
                break;
            case SampleTest::STATUS_RECEIVED:
                $data['received_at'] = now();
                break;
            case SampleTest::STATUS_TESTING:
                $data['tested_at'] = now();
                break;
            case SampleTest::STATUS_DONE:
                $data['done_at'] = now();
                break;
            case SampleTest::STATUS_REJECTED:
                $data['rejected_at'] = now();
                break;
        }

        $test->update($data);

        $test->user->notify(new BorrowingNotification(
            'Status Pengujian Diperbarui',
            "Pengujian sampel {$test->code} telah ditandai '{$test->status_label}' oleh laboran.",
            route('sample-tests.show', $test),
            notifyViaEmail: true,
        ));

        // Beri tahu admin bahwa status pengujian diubah oleh laboran.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                'Status Pengujian Diubah',
                "Pengujian sampel {$test->code} ditandai '{$test->status_label}' oleh {$test->laboran->name}.",
                route('admin.sample-tests.show', $test),
                notifyViaEmail: true,
            ));
        }

        return back()->with('success', 'Status pengujian '.$test->code.' diperbarui menjadi '.$test->status_label.'.');
    }

    /**
     * Ubah status pembayaran pengujian yang ditugaskan ke laboran ini.
     */
    public function updatePayment(Request $request, SampleTest $test)
    {
        abort_unless($test->laboran_id === auth()->id(), 403);

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
                ? "Pembayaran pengujian sampel {$test->code} telah ditandai lunas oleh laboran."
                : "Status pembayaran pengujian sampel {$test->code} diubah menjadi belum dibayar.",
            route('sample-tests.show', $test),
            notifyViaEmail: true,
        ));

        // Beri tahu admin bahwa pembayaran pengujian diubah oleh laboran.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                $paid ? 'Pembayaran Pengujian Lunas' : 'Pembayaran Pengujian Belum Dibayar',
                "Pembayaran pengujian sampel {$test->code} ditandai ".($paid ? 'lunas' : 'belum dibayar')." oleh {$test->laboran->name}.",
                route('admin.sample-tests.show', $test),
                notifyViaEmail: true,
            ));
        }

        return back()->with('success', 'Status pembayaran '.$test->code.' diperbarui menjadi '.SampleTest::paymentStatusLabel($validated['payment_status']).'.');
    }

    /**
     * Halaman cetak label sampel untuk pengujian yang ditugaskan ke laboran ini.
     */
    public function print(SampleTest $test)
    {
        abort_unless($test->laboran_id === auth()->id(), 403);

        $test->load(['user', 'items.parameter.unit', 'items.sampleForm', 'items.sampleType']);

        $backUrl = route('laboran.index');

        return view('sample-tests.print', compact('test', 'backUrl'));
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
