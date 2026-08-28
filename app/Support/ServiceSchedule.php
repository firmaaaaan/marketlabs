<?php

namespace App\Support;

use App\Models\ExaminerWeeklySchedule;
use App\Models\HealthCheckup;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Pengaturan jadwal layanan pemeriksaan kesehatan.
 * Nilai disimpan di tabel settings (key: schedule_*).
 */
class ServiceSchedule
{
    /** Nama hari, key mengikuti dayOfWeekIso (1 = Senin ... 7 = Minggu). */
    public const DAY_NAMES = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public static function enabled(): bool
    {
        return Setting::get('schedule_enabled') === '1';
    }

    /**
     * @return array<int, int> Daftar hari operasional (1 = Senin ... 7 = Minggu).
     */
    public static function days(): array
    {
        $raw = Setting::get('schedule_days', '1,2,3,4,5');

        return array_values(array_filter(
            array_map('intval', explode(',', (string) $raw)),
            fn ($day) => $day >= 1 && $day <= 7,
        ));
    }

    public static function openTime(): string
    {
        return Setting::get('schedule_open_time', '08:00') ?: '08:00';
    }

    public static function closeTime(): string
    {
        return Setting::get('schedule_close_time', '14:00') ?: '14:00';
    }

    public static function quota(): int
    {
        return max(0, (int) Setting::get('schedule_quota', 0));
    }

    /**
     * Penugasan pemeriksa otomatis aktif? (1 pemeriksa pegang beberapa orang secara acak).
     */
    public static function autoAssign(): bool
    {
        return Setting::get('schedule_auto_assign') === '1';
    }

    /**
     * Estimasi lama pemeriksaan per orang (menit).
     */
    public static function duration(): int
    {
        return max(1, (int) Setting::get('schedule_duration', 5));
    }

    public static function breakStart(): ?string
    {
        $value = Setting::get('schedule_break_start', '');

        return $value !== '' ? $value : null;
    }

    public static function breakEnd(): ?string
    {
        $value = Setting::get('schedule_break_end', '');

        return $value !== '' ? $value : null;
    }

    /**
     * Pemeriksa (laboran) yang bertugas pada tanggal tertentu.
     * Pola mingguan per bulan: hari pada tanggal tersebut (mis. Senin) mengambil
     * pemeriksa dari jadwal bulan tanggal tersebut.
     */
    public static function examinersOn($date): Collection
    {
        $date = Carbon::parse($date);

        return self::weeklyExaminers($date->format('Y-m'), $date->dayOfWeekIso);
    }

    /**
     * Pemeriksa yang bertugas pada hari tertentu (1 = Senin ... 7 = Minggu) di bulan tertentu (YYYY-MM).
     */
    public static function weeklyExaminers(string $month, int $dayOfWeek): Collection
    {
        return ExaminerWeeklySchedule::with('user')
            ->where('month', $month)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('user_id')
            ->get()
            ->pluck('user')
            ->values();
    }

    /**
     * Penugasan otomatis: pilih pemeriksa secara acak namun merata
     * (pemeriksa dengan beban paling ringan, diacak bila sama).
     */
    public static function assignExaminer(HealthCheckup $checkup): ?int
    {
        if (! self::autoAssign()) {
            return null;
        }

        $examiners = self::examinersOn($checkup->booking_date)->shuffle();

        if ($examiners->isEmpty()) {
            return null;
        }

        $best = null;
        $bestCount = PHP_INT_MAX;

        foreach ($examiners as $examiner) {
            $count = HealthCheckup::where('examiner_id', $examiner->id)
                ->whereDate('booking_date', $checkup->booking_date)
                ->whereNotIn('status', [HealthCheckup::STATUS_CANCELLED, HealthCheckup::STATUS_REJECTED])
                ->count();

            if ($count < $bestCount) {
                $bestCount = $count;
                $best = $examiner->id;
            }
        }

        $checkup->update(['examiner_id' => $best]);

        return $best;
    }

    /**
     * Info antrian live untuk tanggal tertentu: posisi pasien dihitung dari
     * booking yang masih aktif (belum selesai). Pasien yang sudah selesai (done)
     * otomatis keluar dari antrian sehingga posisi yang di belakangnya berkurang.
     *
     * @return array<int, array{position: ?int, people_ahead: ?int, waiting: int}>
     */
    public static function queueInfo($date): array
    {
        $bookings = HealthCheckup::with('examiner')
            ->whereDate('booking_date', Carbon::parse($date)->toDateString())
            ->whereNotIn('status', [HealthCheckup::STATUS_CANCELLED, HealthCheckup::STATUS_REJECTED])
            ->orderBy('queue_number')
            ->get();

        $waiting = $bookings->where('status', '!=', HealthCheckup::STATUS_DONE)->count();

        $result = [];
        $position = 0;

        foreach ($bookings as $booking) {
            if ($booking->status === HealthCheckup::STATUS_DONE) {
                $result[$booking->id] = [
                    'position' => null,
                    'people_ahead' => null,
                    'waiting' => $waiting,
                ];

                continue;
            }

            $position++;

            $result[$booking->id] = [
                'position' => $position,
                'people_ahead' => $position - 1,
                'waiting' => $waiting,
            ];
        }

        return $result;
    }

    /**
     * Pratinjau antrian untuk booking berikutnya pada tanggal tertentu (tanpa
     * menyimpan), dipakai untuk pratinjau live di form booking. Posisi = jumlah
     * antrian aktif + 1. Pemeriksa target meniru penugasan otomatis (beban
     * paling ringan), dipilih deterministik untuk pratinjau.
     *
     * @return array{queue_number: int, queue_label: string, position: int, people_ahead: int, waiting: int, examiner_id: ?int, examiner_name: ?string}
     */
    public static function nextQueue($date): array
    {
        $date = Carbon::parse($date);

        $bookings = HealthCheckup::with('examiner')
            ->whereDate('booking_date', $date->toDateString())
            ->whereNotIn('status', [HealthCheckup::STATUS_CANCELLED, HealthCheckup::STATUS_REJECTED])
            ->orderBy('queue_number')
            ->get();

        $waiting = $bookings->where('status', '!=', HealthCheckup::STATUS_DONE)->count();

        $queueNumber = (int) HealthCheckup::whereDate('booking_date', $date->toDateString())->max('queue_number') + 1;

        // Pemeriksa target: meniru penugasan otomatis (beban paling ringan).
        $target = null;
        $targetName = null;

        if (self::autoAssign()) {
            $examiners = self::examinersOn($date)->sortBy('id')->values();

            if ($examiners->isNotEmpty()) {
                $best = null;
                $bestCount = PHP_INT_MAX;

                foreach ($examiners as $examiner) {
                    $count = $bookings->where('examiner_id', $examiner->id)->count();

                    if ($count < $bestCount) {
                        $bestCount = $count;
                        $best = $examiner;
                    }
                }

                $target = $best->id;
                $targetName = $best->name;
            }
        }

        return [
            'queue_number' => $queueNumber,
            'queue_label' => 'Q-'.str_pad((string) $queueNumber, 3, '0', STR_PAD_LEFT),
            'position' => $waiting + 1,
            'people_ahead' => $waiting,
            'waiting' => $waiting + 1,
            'examiner_id' => $target,
            'examiner_name' => $targetName,
        ];
    }

    /**
     * Apakah tanggal tertentu termasuk hari operasional?
     */
    public static function isOpenOn($date): bool
    {
        $day = Carbon::parse($date)->dayOfWeekIso;

        return in_array($day, self::days(), true);
    }

    /**
     * Jumlah booking aktif (tidak dibatalkan/ditolak) pada tanggal tertentu.
     */
    public static function bookingsOn($date): int
    {
        return HealthCheckup::whereDate('booking_date', Carbon::parse($date)->toDateString())
            ->whereNotIn('status', [HealthCheckup::STATUS_CANCELLED, HealthCheckup::STATUS_REJECTED])
            ->count();
    }

    /**
     * Apakah kuota tanggal tertentu masih tersedia?
     */
    public static function hasQuota($date): bool
    {
        $quota = self::quota();

        if ($quota <= 0) {
            return true;
        }

        return self::bookingsOn($date) < $quota;
    }

    /**
     * Nama hari operasional, mis. "Senin, Selasa, Rabu, Kamis, Jumat".
     */
    public static function dayNames(): string
    {
        $names = array_map(fn ($day) => self::DAY_NAMES[$day] ?? '', self::days());

        return implode(', ', array_filter($names));
    }

    /**
     * Ringkasan jadwal untuk ditampilkan ke pengguna.
     *
     * @return array{enabled: bool, day_names: string, days: array<int, int>, open_time: string, close_time: string, quota: int, duration: int, break_start: ?string, break_end: ?string, auto_assign: bool}
     */
    public static function summary(): array
    {
        return [
            'enabled' => self::enabled(),
            'day_names' => self::dayNames(),
            'days' => self::days(),
            'open_time' => self::openTime(),
            'close_time' => self::closeTime(),
            'quota' => self::quota(),
            'duration' => self::duration(),
            'break_start' => self::breakStart(),
            'break_end' => self::breakEnd(),
            'auto_assign' => self::autoAssign(),
        ];
    }
}
