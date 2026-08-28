<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExaminerWeeklySchedule;
use App\Models\Setting;
use App\Models\User;
use App\Support\ServiceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AdminScheduleController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));

        if (! preg_match('/^\d{4}-\d{2}$/', (string) $month)) {
            $month = now()->format('Y-m');
        }

        $monthDate = Carbon::createFromFormat('Y-m', $month);

        // Jadwal mingguan bulan terpilih, dikelompokkan per hari (1 = Senin ... 7 = Minggu).
        $weekly = ExaminerWeeklySchedule::with('user')
            ->where('month', $month)
            ->orderBy('day_of_week')
            ->orderBy('user_id')
            ->get()
            ->groupBy('day_of_week');

        $previousMonth = $monthDate->copy()->subMonth()->format('Y-m');
        $hasPrevious = ExaminerWeeklySchedule::where('month', $previousMonth)->exists();

        // Pemeriksa diambil dari user ber-role laboran.
        $examiners = User::laboran()->orderBy('name')->get();

        return view('admin.settings.schedule', [
            'month' => $month,
            'previousMonth' => $previousMonth,
            'hasPrevious' => $hasPrevious,
            'weekly' => $weekly,
            'examiners' => $examiners,
            'enabled' => ServiceSchedule::enabled(),
            'days' => ServiceSchedule::days(),
            'openTime' => ServiceSchedule::openTime(),
            'closeTime' => ServiceSchedule::closeTime(),
            'quota' => ServiceSchedule::quota(),
            'autoAssign' => ServiceSchedule::autoAssign(),
            'duration' => ServiceSchedule::duration(),
            'breakStart' => ServiceSchedule::breakStart(),
            'breakEnd' => ServiceSchedule::breakEnd(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'schedule_enabled' => ['sometimes', 'boolean'],
            'schedule_auto_assign' => ['sometimes', 'boolean'],
            'days' => ['nullable', 'array'],
            'days.*' => ['integer', 'between:1,7'],
            'open_time' => ['nullable', 'date_format:H:i'],
            'close_time' => ['nullable', 'date_format:H:i'],
            'quota' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:600'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
        ]);

        $enabled = $request->boolean('schedule_enabled');
        $autoAssign = $request->boolean('schedule_auto_assign');
        $openDays = array_values(array_unique(array_map('intval', $validated['days'] ?? [])));
        sort($openDays);

        $openTime = trim((string) ($validated['open_time'] ?? ''));
        $closeTime = trim((string) ($validated['close_time'] ?? ''));
        $quota = (int) ($validated['quota'] ?? 0);
        $duration = (int) ($validated['duration'] ?? 5);
        $breakStart = trim((string) ($validated['break_start'] ?? ''));
        $breakEnd = trim((string) ($validated['break_end'] ?? ''));

        if ($enabled && empty($openDays)) {
            return back()->withErrors(['days' => 'Pilih minimal satu hari operasional.'])->withInput();
        }

        if ($enabled && ($openTime === '' || $closeTime === '')) {
            return back()->withErrors(['open_time' => 'Jam layanan wajib diisi saat jadwal aktif.'])->withInput();
        }

        if ($openTime !== '' && $closeTime !== '' && $closeTime <= $openTime) {
            return back()->withErrors(['close_time' => 'Jam tutup harus setelah jam buka.'])->withInput();
        }

        if ($enabled && $quota < 1) {
            return back()->withErrors(['quota' => 'Kuota per hari minimal 1 saat jadwal aktif.'])->withInput();
        }

        if ($enabled && $duration < 1) {
            return back()->withErrors(['duration' => 'Durasi pemeriksaan minimal 1 menit.'])->withInput();
        }

        if ($breakStart !== '' && $breakEnd !== '' && $breakEnd <= $breakStart) {
            return back()->withErrors(['break_end' => 'Jam selesai istirahat harus setelah jam mulai istirahat.'])->withInput();
        }

        Setting::set('schedule_enabled', $enabled ? '1' : '0');
        Setting::set('schedule_auto_assign', $autoAssign ? '1' : '0');
        Setting::set('schedule_days', implode(',', $openDays));
        Setting::set('schedule_open_time', $openTime);
        Setting::set('schedule_close_time', $closeTime);
        Setting::set('schedule_quota', (string) $quota);
        Setting::set('schedule_duration', (string) $duration);
        Setting::set('schedule_break_start', $breakStart);
        Setting::set('schedule_break_end', $breakEnd);

        return redirect()->route('admin.schedule.index')
            ->with('success', 'Jadwal layanan berhasil disimpan.');
    }

    /**
     * Simpan pola pemeriksa mingguan untuk satu bulan: days[hari][] = id pemeriksa.
     * Mengganti seluruh pola bulan tersebut.
     */
    public function storeWeekly(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'days' => ['nullable', 'array'],
            'days.*' => ['array'],
            'days.*.*' => ['string', Rule::exists('users', 'id')->where('role', User::ROLE_LABORAN)],
        ]);

        $month = $validated['month'];

        // Hapus seluruh pola lama satu per satu agar event model terpicu (tercatat di log aktivitas).
        ExaminerWeeklySchedule::where('month', $month)->get()->each->delete();

        foreach (range(1, 7) as $dayOfWeek) {
            $userIds = array_values(array_unique($validated['days'][$dayOfWeek] ?? []));

            foreach ($userIds as $userId) {
                ExaminerWeeklySchedule::create([
                    'user_id' => $userId,
                    'month' => $month,
                    'day_of_week' => $dayOfWeek,
                ]);
            }
        }

        return redirect()->route('admin.schedule.index', ['month' => $month])
            ->with('success', 'Jadwal pemeriksa untuk bulan '.Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y').' berhasil disimpan.');
    }

    /**
     * Salin jadwal pemeriksa dari bulan sebelumnya ke bulan terpilih (untuk pembaruan bulanan).
     */
    public function copyPrevious(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $month = $validated['month'];
        $previousMonth = Carbon::createFromFormat('Y-m', $month)->subMonth()->format('Y-m');

        $copied = 0;

        foreach (ExaminerWeeklySchedule::where('month', $previousMonth)->get() as $schedule) {
            $inserted = ExaminerWeeklySchedule::firstOrCreate(
                ['month' => $month, 'day_of_week' => $schedule->day_of_week, 'user_id' => $schedule->user_id],
                ['month' => $month, 'day_of_week' => $schedule->day_of_week, 'user_id' => $schedule->user_id],
            );

            if ($inserted->wasRecentlyCreated) {
                $copied++;
            }
        }

        return redirect()->route('admin.schedule.index', ['month' => $month])
            ->with('success', $copied > 0
                ? "$copied penugasan berhasil disalin dari bulan ".Carbon::createFromFormat('Y-m', $previousMonth)->translatedFormat('F Y').'.'
                : 'Tidak ada penugasan baru untuk disalin (sudah sama dengan bulan sebelumnya).');
    }

    public function destroyWeekly(ExaminerWeeklySchedule $schedule)
    {
        $month = $schedule->month;
        $schedule->delete();

        return redirect()->route('admin.schedule.index', ['month' => $month])
            ->with('success', 'Penugasan pemeriksa berhasil dihapus.');
    }
}
