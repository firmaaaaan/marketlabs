<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use App\Models\HealthCheckup;
use App\Models\ResearchProposal;
use App\Models\SampleTest;
use App\Notifications\BorrowingNotification;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'app:send-reminders';

    protected $description = 'Kirim pengingat otomatis untuk peminjaman terlambat, jadwal kesehatan, riset, dan invoice belum dibayar';

    public function handle(): int
    {
        $this->sendOverdueBorrowingReminders();
        $this->sendUpcomingReturnReminders();
        $this->sendUpcomingHealthCheckupReminders();
        $this->sendResearchEndingReminders();
        $this->sendUnpaidInvoiceReminders();

        $this->info('Semua pengingat berhasil dikirim.');

        return self::SUCCESS;
    }

    protected function sendOverdueBorrowingReminders(): void
    {
        $overdue = Borrowing::with('user')
            ->where('status', Borrowing::STATUS_BORROWED)
            ->where('return_date', '<', now()->toDateString())
            ->get();

        foreach ($overdue as $borrowing) {
            $days = now()->diffInDays($borrowing->return_date);
            $borrowing->user->notify(new BorrowingNotification(
                'Peminjaman Terlambat',
                "Peminjaman {$borrowing->code} sudah melewati batas waktu pengembalian {$days} hari. Harap segera mengembalikan alat.",
                route('borrowings.show', $borrowing),
                notifyViaEmail: true,
            ));
        }

        if ($overdue->isNotEmpty()) {
            $this->info("Pengingat peminjaman terlambat: {$overdue->count()} item.");
        }
    }

    protected function sendUpcomingReturnReminders(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $upcoming = Borrowing::with('user')
            ->where('status', Borrowing::STATUS_BORROWED)
            ->whereDate('return_date', $tomorrow)
            ->get();

        foreach ($upcoming as $borrowing) {
            $borrowing->user->notify(new BorrowingNotification(
                'Pengingat Pengembalian Besok',
                "Peminjaman {$borrowing->code} harus dikembalikan besok ({$borrowing->return_date->translatedFormat('d M Y')}). Pastikan alat dikembalikan tepat waktu.",
                route('borrowings.show', $borrowing),
                notifyViaEmail: true,
            ));
        }

        if ($upcoming->isNotEmpty()) {
            $this->info("Pengingat pengembalian besok: {$upcoming->count()} item.");
        }
    }

    protected function sendUpcomingHealthCheckupReminders(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $upcoming = HealthCheckup::with('user')
            ->where('status', HealthCheckup::STATUS_APPROVED)
            ->whereDate('booking_date', $tomorrow)
            ->get();

        foreach ($upcoming as $checkup) {
            $checkup->user->notify(new BorrowingNotification(
                'Jadwal Pemeriksaan Kesehatan Besok',
                "Anda memiliki jadwal pemeriksaan kesehatan ({$checkup->code}) pada besok ({$checkup->booking_date->translatedFormat('d M Y')}). Harap hadir tepat waktu.",
                route('health-checkups.show', $checkup),
                notifyViaEmail: true,
            ));
        }

        if ($upcoming->isNotEmpty()) {
            $this->info("Pengingat jadwal kesehatan besok: {$upcoming->count()} item.");
        }
    }

    protected function sendResearchEndingReminders(): void
    {
        $threeDaysLater = now()->addDays(3)->toDateString();

        $ending = ResearchProposal::with('user')
            ->where('status', ResearchProposal::STATUS_ONGOING)
            ->whereDate('end_date', '<=', $threeDaysLater)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->get();

        foreach ($ending as $proposal) {
            $remaining = now()->diffInDays($proposal->end_date);
            $proposal->user->notify(new BorrowingNotification(
                'Riset Akan Berakhir',
                "Riset {$proposal->code} \"{$proposal->title}\" akan berakhir dalam {$remaining} hari ({$proposal->end_date->translatedFormat('d M Y')}). Harap selesaikan riset Anda.",
                route('research.show', $proposal),
                notifyViaEmail: true,
            ));
        }

        if ($ending->isNotEmpty()) {
            $this->info("Pengingat riset berakhir: {$ending->count()} item.");
        }
    }

    protected function sendUnpaidInvoiceReminders(): void
    {
        $threeDaysAgo = now()->subDays(3)->toDateString();

        $unpaidBorrowings = Borrowing::with('user')
            ->where('payment_status', Borrowing::PAYMENT_UNPAID)
            ->where('status', '!=', Borrowing::STATUS_PENDING)
            ->whereDate('created_at', '<=', $threeDaysAgo)
            ->get();

        foreach ($unpaidBorrowings as $borrowing) {
            $borrowing->user->notify(new BorrowingNotification(
                'Invoice Belum Dibayar',
                "Peminjaman {$borrowing->code} memiliki invoice yang belum dibayar. Total: {$borrowing->formatted_total_cost}. Silakan lakukan pembayaran.",
                route('borrowings.show', $borrowing),
                notifyViaEmail: true,
            ));
        }

        $unpaidTests = SampleTest::with('user')
            ->where('payment_status', SampleTest::PAYMENT_UNPAID)
            ->where('status', SampleTest::STATUS_DONE)
            ->whereDate('created_at', '<=', $threeDaysAgo)
            ->get();

        foreach ($unpaidTests as $test) {
            $test->user->notify(new BorrowingNotification(
                'Invoice Belum Dibayar',
                "Pengujian sampel {$test->code} memiliki invoice yang belum dibayar. Total: {$test->formatted_total_cost}. Silakan lakukan pembayaran.",
                route('sample-tests.show', $test),
                notifyViaEmail: true,
            ));
        }

        $total = $unpaidBorrowings->count() + $unpaidTests->count();
        if ($total > 0) {
            $this->info("Pengingat invoice belum dibayar: {$total} item.");
        }
    }
}
