<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\HealthCheckup;
use App\Models\ResearchProposal;
use App\Models\SampleTest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminAnalyticsController extends Controller
{
    private const SERVICES = ['all', 'peminjaman', 'pengujian', 'riset', 'kesehatan'];

    private const SERVICE_LABELS = [
        'all' => 'Semua Layanan',
        'peminjaman' => 'Peminjaman Alat',
        'pengujian' => 'Pengujian Sampel',
        'riset' => 'Riset & Penelitian',
        'kesehatan' => 'Pemeriksaan Kesehatan',
    ];

    public function index(Request $request)
    {
        $service = $this->resolveService($request->query('service'));

        return view('admin.analytics', [
            'service' => $service,
            'serviceLabel' => self::SERVICE_LABELS[$service],
            'services' => self::SERVICE_LABELS,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $service = $this->resolveService($request->query('service'));

        $summary = $this->getSummary($service);
        $revenuePerMonth = $this->getRevenuePerMonth(6, $service);
        $statusBreakdown = $this->getStatusBreakdown($service);
        $revenuePerService = $this->getRevenuePerService();
        $topTools = $this->getTopBorrowedTools(5);

        return response()->json([
            'service' => $service,
            'serviceLabel' => self::SERVICE_LABELS[$service],
            'summary' => $summary,
            'revenuePerMonth' => $revenuePerMonth,
            'statusBreakdown' => $statusBreakdown,
            'revenuePerService' => $revenuePerService,
            'topTools' => $topTools,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $service = $this->resolveService($request->query('service'));
        $spreadsheet = new Spreadsheet;

        // Sheet 1: Summary
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan');

        $title = 'Laporan Analitik MarketLabs';
        if ($service !== 'all') {
            $title .= ' - '.self::SERVICE_LABELS[$service];
        }
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Dicetak: '.now()->format('d M Y H:i'));
        $sheet->mergeCells('A2:D2');

        $sheet->setCellValue('A4', 'Metrik');
        $sheet->setCellValue('B4', 'Nilai');
        $sheet->getStyle('A4:B4')->getFont()->setBold(true);

        $summary = $this->getSummary($service);
        $summaryData = [
            [$summary['revenue_label'], 'Rp '.number_format($summary['revenue'], 0, ',', '.')],
            [$summary['transaction_label'], number_format($summary['transactions'])],
            ['Rata-rata per '.$summary['transaction_label'], 'Rp '.number_format($summary['avg'], 0, ',', '.')],
        ];

        if ($service === 'all') {
            $summaryData[] = ['Total Pengguna', number_format(User::count())];
        }

        foreach ($summaryData as $i => $row) {
            $sheet->setCellValue('A'.($i + 5), $row[0]);
            $sheet->setCellValue('B'.($i + 5), $row[1]);
        }

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);

        // Sheet 2: Revenue per month
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Pendapatan per Bulan');

        $sheet2->setCellValue('A1', 'Bulan');
        $sheet2->setCellValue('B1', 'Pendapatan');
        $sheet2->getStyle('A1:B1')->getFont()->setBold(true);

        $revenueData = $this->getRevenuePerMonth(12, $service);
        foreach ($revenueData as $i => $row) {
            $sheet2->setCellValue('A'.($i + 2), $row['month']);
            $sheet2->setCellValue('B'.($i + 2), 'Rp '.number_format($row['revenue'], 0, ',', '.'));
        }

        $sheet2->getColumnDimension('A')->setWidth(20);
        $sheet2->getColumnDimension('B')->setWidth(25);

        // Sheet 3: Revenue per service (only when 'all')
        if ($service === 'all') {
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('Pendapatan per Layanan');

            $sheet3->setCellValue('A1', 'Layanan');
            $sheet3->setCellValue('B1', 'Pendapatan');
            $sheet3->setCellValue('C1', 'Jumlah Transaksi');
            $sheet3->getStyle('A1:C1')->getFont()->setBold(true);

            $serviceData = $this->getRevenuePerServiceDetailed();
            foreach ($serviceData as $i => $row) {
                $sheet3->setCellValue('A'.($i + 2), $row['service']);
                $sheet3->setCellValue('B'.($i + 2), 'Rp '.number_format($row['revenue'], 0, ',', '.'));
                $sheet3->setCellValue('C'.($i + 2), $row['count']);
            }

            $sheet3->getColumnDimension('A')->setWidth(25);
            $sheet3->getColumnDimension('B')->setWidth(25);
            $sheet3->getColumnDimension('C')->setWidth(20);
        }

        // Sheet 4: Status breakdown
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Status Transaksi');

        $sheet4->setCellValue('A1', 'Status');
        $sheet4->setCellValue('B1', 'Jumlah');
        $sheet4->getStyle('A1:B1')->getFont()->setBold(true);

        $statusData = $this->getStatusBreakdown($service);
        foreach ($statusData as $i => $row) {
            $sheet4->setCellValue('A'.($i + 2), $row['label']);
            $sheet4->setCellValue('B'.($i + 2), $row['count']);
        }

        $sheet4->getColumnDimension('A')->setWidth(30);
        $sheet4->getColumnDimension('B')->setWidth(15);

        $suffix = $service !== 'all' ? '_'.ucfirst($service) : '';
        $filename = 'Laporan_Analitik'.$suffix.'_'.now()->format('Y-m-d_H-i').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $service = $this->resolveService($request->query('service'));
        $summary = $this->getSummary($service);

        $data = [
            'service' => $service,
            'serviceLabel' => self::SERVICE_LABELS[$service],
            'summary' => $summary,
            'revenuePerMonth' => $this->getRevenuePerMonth(6, $service),
            'statusBreakdown' => $this->getStatusBreakdown($service),
            'revenuePerService' => $this->getRevenuePerServiceDetailed(),
            'topTools' => $this->getTopBorrowedTools(5),
            'generatedAt' => now()->format('d M Y H:i'),
        ];

        $pdf = Pdf::loadView('admin.analytics-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        $suffix = $service !== 'all' ? '_'.ucfirst($service) : '';

        return $pdf->download('Laporan_Analitik'.$suffix.'_'.now()->format('Y-m-d').'.pdf');
    }

    private function resolveService(?string $value): string
    {
        $value = strtolower(trim($value ?? ''));

        return in_array($value, self::SERVICES) ? $value : 'all';
    }

    private function getSummary(string $service): array
    {
        return match ($service) {
            'peminjaman' => [
                'revenue' => $this->getServiceRevenue('peminjaman'),
                'revenue_label' => 'Total Pendapatan Peminjaman',
                'transactions' => Borrowing::count(),
                'transaction_label' => 'Peminjaman',
                'avg' => $this->getServiceAvg('peminjaman'),
                'extra' => number_format(BorrowingItem::sum('quantity')).' unit dipinjam',
            ],
            'pengujian' => [
                'revenue' => $this->getServiceRevenue('pengujian'),
                'revenue_label' => 'Total Pendapatan Pengujian',
                'transactions' => SampleTest::count(),
                'transaction_label' => 'Pengujian',
                'avg' => $this->getServiceAvg('pengujian'),
                'extra' => SampleTest::sum(DB::raw('(SELECT COUNT(*) FROM sample_test_items WHERE sample_test_items.sample_test_id = sample_tests.id)')).' parameter diuji',
            ],
            'riset' => [
                'revenue' => $this->getServiceRevenue('riset'),
                'revenue_label' => 'Total Pendapatan Riset',
                'transactions' => ResearchProposal::count(),
                'transaction_label' => 'Riset',
                'avg' => $this->getServiceAvg('riset'),
                'extra' => ResearchProposal::where('status', 'ongoing')->count().' riset berlangsung',
            ],
            'kesehatan' => [
                'revenue' => $this->getServiceRevenue('kesehatan'),
                'revenue_label' => 'Total Pendapatan Kesehatan',
                'transactions' => HealthCheckup::count(),
                'transaction_label' => 'Pemeriksaan',
                'avg' => $this->getServiceAvg('kesehatan'),
                'extra' => HealthCheckup::where('status', 'done')->count().' pemeriksaan selesai',
            ],
            default => [
                'revenue' => $this->getServiceRevenue('all'),
                'revenue_label' => 'Total Pendapatan',
                'transactions' => $this->getTotalTransactions(),
                'transaction_label' => 'Transaksi',
                'avg' => $this->getTotalTransactions() > 0 ? round($this->getServiceRevenue('all') / $this->getTotalTransactions()) : 0,
                'extra' => User::count().' pengguna terdaftar',
            ],
        };
    }

    private function getTotalTransactions(): int
    {
        return Borrowing::count()
            + SampleTest::count()
            + ResearchProposal::count()
            + HealthCheckup::count();
    }

    private function getServiceRevenue(string $service): int
    {
        return match ($service) {
            'peminjaman' => Borrowing::where('payment_status', Borrowing::PAYMENT_PAID)
                ->get()->sum('total_cost'),
            'pengujian' => SampleTest::where('payment_status', SampleTest::PAYMENT_PAID)
                ->sum('total_cost'),
            'riset' => ResearchProposal::where('payment_status', ResearchProposal::PAYMENT_PAID)
                ->get()->sum('grand_total'),
            'kesehatan' => $this->getHealthRevenue(),
            default => $this->getAllRevenue(),
        };
    }

    private function getAllRevenue(): int
    {
        return $this->getServiceRevenue('peminjaman')
            + $this->getServiceRevenue('pengujian')
            + $this->getServiceRevenue('riset')
            + $this->getServiceRevenue('kesehatan');
    }

    private function getHealthRevenue(): int
    {
        $healthCheckups = HealthCheckup::where('payment_status', HealthCheckup::PAYMENT_PAID)
            ->with('type')
            ->get();

        $revenue = 0;
        foreach ($healthCheckups as $hc) {
            $revenue += $hc->type->price ?? 0;
        }

        return $revenue;
    }

    private function getServiceAvg(string $service): int
    {
        $count = match ($service) {
            'peminjaman' => Borrowing::count(),
            'pengujian' => SampleTest::count(),
            'riset' => ResearchProposal::count(),
            'kesehatan' => HealthCheckup::count(),
            default => $this->getTotalTransactions(),
        };

        $revenue = $this->getServiceRevenue($service);

        return $count > 0 ? round($revenue / $count) : 0;
    }

    private function getRevenuePerMonth(int $months = 6, string $service = 'all'): array
    {
        $result = [];
        $now = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end = $now->copy()->subMonths($i)->endOfMonth();

            $revenue = match ($service) {
                'peminjaman' => Borrowing::where('payment_status', Borrowing::PAYMENT_PAID)
                    ->whereBetween('created_at', [$start, $end])
                    ->get()->sum('total_cost'),
                'pengujian' => SampleTest::where('payment_status', SampleTest::PAYMENT_PAID)
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('total_cost'),
                'riset' => ResearchProposal::where('payment_status', ResearchProposal::PAYMENT_PAID)
                    ->whereBetween('created_at', [$start, $end])
                    ->get()->sum('grand_total'),
                'kesehatan' => $this->getHealthRevenueBetween($start, $end),
                default => $this->getAllRevenueBetween($start, $end),
            };

            $result[] = [
                'month' => $start->translatedFormat('M Y'),
                'revenue' => $revenue,
            ];
        }

        return $result;
    }

    private function getHealthRevenueBetween(Carbon $start, Carbon $end): int
    {
        $healthCheckups = HealthCheckup::where('payment_status', HealthCheckup::PAYMENT_PAID)
            ->whereBetween('created_at', [$start, $end])
            ->with('type')
            ->get();

        $revenue = 0;
        foreach ($healthCheckups as $hc) {
            $revenue += $hc->type->price ?? 0;
        }

        return $revenue;
    }

    private function getAllRevenueBetween(Carbon $start, Carbon $end): int
    {
        $borrowingRevenue = Borrowing::where('payment_status', Borrowing::PAYMENT_PAID)
            ->whereBetween('created_at', [$start, $end])
            ->get()->sum('total_cost');

        $sampleTestRevenue = SampleTest::where('payment_status', SampleTest::PAYMENT_PAID)
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_cost');

        $researchRevenue = ResearchProposal::where('payment_status', ResearchProposal::PAYMENT_PAID)
            ->whereBetween('created_at', [$start, $end])
            ->get()->sum('grand_total');

        $healthRevenue = $this->getHealthRevenueBetween($start, $end);

        return $borrowingRevenue + $sampleTestRevenue + $researchRevenue + $healthRevenue;
    }

    private function getStatusBreakdown(string $service = 'all'): array
    {
        if ($service === 'peminjaman' || $service === 'all') {
            $borrowingStatuses = Borrowing::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(fn ($item) => ['status' => 'borrowing_'.$item->status, 'label' => Borrowing::statusLabel($item->status), 'count' => $item->count]);
        }

        if ($service === 'pengujian' || $service === 'all') {
            $sampleStatuses = SampleTest::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(fn ($item) => ['status' => 'sample_'.$item->status, 'label' => SampleTest::statusLabel($item->status), 'count' => $item->count]);
        }

        if ($service === 'riset' || $service === 'all') {
            $researchStatuses = ResearchProposal::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(fn ($item) => ['status' => 'research_'.$item->status, 'label' => ResearchProposal::statusLabel($item->status), 'count' => $item->count]);
        }

        if ($service === 'kesehatan' || $service === 'all') {
            $healthStatuses = HealthCheckup::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(fn ($item) => ['status' => 'health_'.$item->status, 'label' => HealthCheckup::statusLabel($item->status), 'count' => $item->count]);
        }

        $result = collect();
        if (isset($borrowingStatuses)) {
            $result = $result->concat($borrowingStatuses);
        }
        if (isset($sampleStatuses)) {
            $result = $result->concat($sampleStatuses);
        }
        if (isset($researchStatuses)) {
            $result = $result->concat($researchStatuses);
        }
        if (isset($healthStatuses)) {
            $result = $result->concat($healthStatuses);
        }

        return $result->toArray();
    }

    private function getRevenuePerService(): array
    {
        return [
            ['service' => 'Peminjaman', 'revenue' => $this->getServiceRevenue('peminjaman')],
            ['service' => 'Pengujian', 'revenue' => $this->getServiceRevenue('pengujian')],
            ['service' => 'Riset', 'revenue' => $this->getServiceRevenue('riset')],
            ['service' => 'Kesehatan', 'revenue' => $this->getServiceRevenue('kesehatan')],
        ];
    }

    private function getRevenuePerServiceDetailed(): array
    {
        return [
            ['service' => 'Peminjaman Alat', 'revenue' => $this->getServiceRevenue('peminjaman'), 'count' => Borrowing::count()],
            ['service' => 'Pengujian Sampel', 'revenue' => $this->getServiceRevenue('pengujian'), 'count' => SampleTest::count()],
            ['service' => 'Riset & Penelitian', 'revenue' => $this->getServiceRevenue('riset'), 'count' => ResearchProposal::count()],
            ['service' => 'Pemeriksaan Kesehatan', 'revenue' => $this->getServiceRevenue('kesehatan'), 'count' => HealthCheckup::count()],
        ];
    }

    private function getTopBorrowedTools(int $limit): array
    {
        return BorrowingItem::select('tool_id', DB::raw('sum(quantity) as total_quantity'))
            ->groupBy('tool_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->with('tool')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->tool?->name ?? 'Unknown',
                'quantity' => $item->total_quantity,
            ])
            ->toArray();
    }
}
