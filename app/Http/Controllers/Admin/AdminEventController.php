<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateCertificateJob;
use App\Jobs\GenerateCertificatesBatchJob;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Support\CertificateRenderer;
use App\Support\ExcelExport;
use App\Support\FormFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminEventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::withCount([
            'registrations' => fn ($q) => $q->where('status', EventRegistration::STATUS_REGISTERED),
        ])->latest();

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        $events = $query->paginate(15)->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateEvent($request);

        Event::create([
            'code' => $this->generateCode(),
            'slug' => $this->generateSlug($validated['title']),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'quota' => $validated['quota'] ?? null,
            'fee' => $validated['fee'] ?? null,
            'discount' => $validated['discount'] ?? null,
            'registration_deadline' => $validated['registration_deadline'] ?? null,
            'status' => $validated['status'],
            'mode' => $validated['mode'] ?? null,
            'image' => $request->hasFile('image') ? $request->file('image')->store('events', 'public') : null,
            'poster' => $request->hasFile('poster') ? $request->file('poster')->store('events/posters', 'public') : null,
            'form_fields' => FormFields::normalize(json_decode($validated['form_fields'] ?? '[]', true) ?: []),
            'attendance_fields' => FormFields::normalize(json_decode($validated['attendance_fields'] ?? '[]', true) ?: []),
            'attendance_enabled' => $request->boolean('attendance_enabled'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $this->validateEvent($request);

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'quota' => $validated['quota'] ?? null,
            'fee' => $validated['fee'] ?? null,
            'discount' => $validated['discount'] ?? null,
            'registration_deadline' => $validated['registration_deadline'] ?? null,
            'status' => $validated['status'],
            'mode' => $validated['mode'] ?? null,
            'form_fields' => FormFields::normalize(json_decode($validated['form_fields'] ?? '[]', true) ?: []),
            'attendance_fields' => FormFields::normalize(json_decode($validated['attendance_fields'] ?? '[]', true) ?: []),
            'attendance_enabled' => $request->boolean('attendance_enabled'),
        ]);

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }

            $event->update(['image' => $request->file('image')->store('events', 'public')]);
        }

        if ($request->hasFile('poster')) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }

            $event->update(['poster' => $request->file('poster')->store('events/posters', 'public')]);
        }

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        if ($event->certificate_template) {
            Storage::disk('public')->delete($event->certificate_template);
        }

        if ($event->certificate_template_back) {
            Storage::disk('public')->delete($event->certificate_template_back);
        }

        if ($event->certificate_font) {
            Storage::disk('public')->delete($event->certificate_font);
        }

        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        if ($event->poster) {
            Storage::disk('public')->delete($event->poster);
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    public function show(Request $request, Event $event)
    {
        $registrations = $event->registrations()
            ->with('user')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.events.show', compact('event', 'registrations'));
    }

    public function updateRegistrationStatus(Event $event, EventRegistration $registration)
    {
        $newStatus = match ($registration->status) {
            EventRegistration::STATUS_PENDING => EventRegistration::STATUS_REGISTERED,
            EventRegistration::STATUS_REGISTERED => EventRegistration::STATUS_CANCELLED,
            EventRegistration::STATUS_CANCELLED => EventRegistration::STATUS_PENDING,
            default => EventRegistration::STATUS_PENDING,
        };

        $registration->update(['status' => $newStatus]);

        return back()->with('success', 'Status peserta diperbarui menjadi '.EventRegistration::statusLabel($newStatus).'.');
    }

    public function bulkUpdateStatus(Request $request, Event $event)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                EventRegistration::STATUS_PENDING,
                EventRegistration::STATUS_REGISTERED,
                EventRegistration::STATUS_CANCELLED,
            ])],
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['string', 'exists:event_registrations,id'],
        ]);

        $count = EventRegistration::whereIn('id', $validated['registration_ids'])
            ->where('event_id', $event->id)
            ->update(['status' => $validated['status']]);

        return back()->with('success', "{$count} peserta berhasil diperbarui menjadi ".EventRegistration::statusLabel($validated['status']).'.');
    }

    public function markAttendance(Request $request, Event $event, EventRegistration $registration)
    {
        $answers = FormFields::validate($request, FormFields::normalize($event->attendance_fields));

        $registration->update([
            'attendance_answers' => array_merge($registration->attendance_answers ?? [], $answers),
            'attended_at' => $registration->attended_at ?? now(),
        ]);

        return back()->with('success', "Presensi {$registration->user->name} berhasil dicatat.");
    }

    public function export(Event $event)
    {
        return $this->exportParticipants($event);
    }

    /**
     * Export data peserta event.
     */
    public function exportParticipants(Event $event)
    {
        $registrations = $event->registrations()
            ->with('user')
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->latest()
            ->get();

        $formFields = FormFields::normalize($event->form_fields);

        $headers = ['No', 'Nama', 'Email', 'NIM/NIP', 'Instansi', 'Hadir'];
        foreach ($formFields as $field) {
            $headers[] = $field['label'];
        }
        $headers[] = 'Tanggal Daftar';

        $rows = [$headers];

        foreach ($registrations as $i => $reg) {
            $answers = $reg->answers ?? [];
            $row = [
                $i + 1,
                $reg->user->name,
                $reg->user->email,
                $reg->user->nim_nip ?? '-',
                $reg->user->institution ?? '-',
                $reg->is_attended ? 'Ya' : 'Tidak',
            ];

            foreach ($formFields as $field) {
                $row[] = $answers[$field['key']] ?? '-';
            }

            $row[] = $reg->created_at->translatedFormat('d M Y H:i');
            $rows[] = $row;
        }

        return ExcelExport::download('peserta-'.$event->slug.'-'.now()->format('Ymd-His').'.xlsx', $rows);
    }

    public function exportAttendance(Event $event)
    {
        $registrations = $event->registrations()
            ->with('user')
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->whereNotNull('attended_at')
            ->orderBy('attended_at')
            ->get();

        $attendanceFields = FormFields::normalize($event->attendance_fields);
        $attendanceHeaders = array_map(fn ($f) => $f['label'], $attendanceFields);

        $headers = array_merge([
            'No', 'Nama', 'Email', 'NIM/NIP', 'Instansi', 'Waktu Hadir',
        ], $attendanceHeaders);

        $rows = [$headers];

        foreach ($registrations as $i => $reg) {
            $attendanceAnswers = $reg->attendance_answers ?? [];
            $row = [
                $i + 1,
                $reg->user->name,
                $reg->user->email,
                $reg->user->nim_nip ?? '-',
                $reg->user->institution ?? '-',
                $reg->attended_at->translatedFormat('d M Y H:i'),
            ];

            foreach ($attendanceFields as $field) {
                $row[] = $attendanceAnswers[$field['key']] ?? '-';
            }

            $rows[] = $row;
        }

        return ExcelExport::download('presensi-'.$event->slug.'-'.now()->format('Ymd-His').'.xlsx', $rows);
    }

    public function certificate(Event $event)
    {
        $preview = $event->certificate_ready
            ? CertificateRenderer::preview($event)
            : ['front' => null, 'back' => null];

        return view('admin.events.certificate', compact('event', 'preview'));
    }

    public function saveCertificate(Request $request, Event $event)
    {
        $validated = $request->validate([
            'certificate_layout' => ['required', 'json'],
            'certificate_layout_back' => ['nullable', 'json'],
        ]);

        $data = [
            'certificate_layout' => $this->normalizeLayout(json_decode($validated['certificate_layout'], true)),
        ];

        if ($request->filled('certificate_layout_back')) {
            $data['certificate_layout_back'] = $this->normalizeLayout(json_decode($validated['certificate_layout_back'], true) ?: []);
        }

        if ($request->hasFile('certificate_template')) {
            $request->validate([
                'certificate_template' => ['image', 'mimes:png,jpg,jpeg', 'max:5120'],
            ]);

            if ($event->certificate_template) {
                Storage::disk('public')->delete($event->certificate_template);
            }

            $data['certificate_template'] = $request->file('certificate_template')->store('events/templates', 'public');
        }

        if ($request->hasFile('certificate_template_back')) {
            $request->validate([
                'certificate_template_back' => ['image', 'mimes:png,jpg,jpeg', 'max:5120'],
            ]);

            if ($event->certificate_template_back) {
                Storage::disk('public')->delete($event->certificate_template_back);
            }

            $data['certificate_template_back'] = $request->file('certificate_template_back')->store('events/templates', 'public');
        }

        $event->update($data);

        return back()->with('success', 'Pengaturan tata letak sertifikat berhasil disimpan.');
    }

    public function deleteCertificateBack(Event $event)
    {
        if ($event->certificate_template_back) {
            Storage::disk('public')->delete($event->certificate_template_back);
        }

        $event->update([
            'certificate_template_back' => null,
            'certificate_layout_back' => null,
        ]);

        $event->registrations()->update(['certificate_back_path' => null]);

        return back()->with('success', 'Sisi belakang sertifikat dihapus.');
    }

    public function generateCertificates(Event $event)
    {
        if (! $event->certificate_ready) {
            return back()->with('error', 'Unggah template & atur tata letak sertifikat terlebih dahulu.');
        }

        if ($event->is_certificate_batch_processing) {
            return back()->with('error', 'Sertifikat sedang diproses. Silakan tunggu hingga selesai.');
        }

        $pendingCount = $event->registrations()
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->whereNotNull('attended_at')
            ->whereNull('certificate_number')
            ->count();

        if ($pendingCount === 0) {
            return back()->with('error', 'Tidak ada peserta hadir yang belum memiliki sertifikat.');
        }

        GenerateCertificatesBatchJob::dispatch($event);

        return back()->with('success', "Queue generate sertifikat telah dimulai untuk {$pendingCount} peserta. Proses berjalan di background.");
    }

    public function generateSingleCertificate(Event $event, EventRegistration $registration)
    {
        if (! $event->certificate_ready) {
            return back()->with('error', 'Unggah template & atur tata letak sertifikat terlebih dahulu.');
        }

        if (! $registration->attended_at) {
            return back()->with('error', 'Peserta belum hadir, sertifikat belum bisa digenerate.');
        }

        if ($registration->certificate_number) {
            return back()->with('error', 'Peserta sudah memiliki sertifikat.');
        }

        GenerateCertificateJob::dispatch($registration);

        return back()->with('success', "Sertifikat untuk {$registration->user->name} sedang diproses di queue.");
    }

    protected function validateEvent(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'quota' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'fee' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'registration_deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in([
                Event::STATUS_DRAFT,
                Event::STATUS_ACTIVE,
                Event::STATUS_CLOSED,
                Event::STATUS_COMPLETED,
            ])],
            'mode' => ['nullable', Rule::in(array_keys(Event::modes()))],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'poster' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
            'form_fields' => ['nullable', 'json'],
            'attendance_fields' => ['nullable', 'json'],
            'attendance_enabled' => ['nullable', 'boolean'],
        ]);

        if (($validated['discount'] ?? null) !== null
            && ($validated['fee'] ?? null) !== null
            && (float) $validated['discount'] > (float) $validated['fee']) {
            throw ValidationException::withMessages([
                'discount' => 'Diskon tidak boleh lebih besar dari biaya pendaftaran.',
            ]);
        }

        return $validated;
    }

    protected function generateCode(): string
    {
        $year = date('Y');

        do {
            $code = 'EVT-'.$year.'-'.str_pad((string) (Event::count() + 1), 3, '0', STR_PAD_LEFT);
        } while (Event::where('code', $code)->exists());

        return $code;
    }

    protected function generateSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'event';
        $slug = $base;
        $i = 1;

        while (Event::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    protected function normalizeLayout(mixed $layout): array
    {
        $aligns = ['left', 'center', 'right'];
        $fonts = array_keys(CertificateRenderer::fontFamilies());
        $weights = ['regular', 'bold'];
        $clean = [];

        foreach (array_values($layout ?? []) as $line) {
            // Hanya nama peserta yang bisa disesuaikan.
            if (($line['type'] ?? null) !== 'name') {
                continue;
            }

            $x = $line['x'] ?? 50;
            $y = $line['y'] ?? 60;
            $size = $line['size'] ?? 44;
            $color = $line['color'] ?? '#1e293b';
            $align = $line['align'] ?? 'center';
            $font = $line['font'] ?? 'lato';
            $weight = $line['weight'] ?? 'bold';
            $enabled = $line['enabled'] ?? true;

            $clean[] = [
                'type' => 'name',
                'x' => min(100, max(0, (float) $x)),
                'y' => min(100, max(0, (float) $y)),
                'size' => min(300, max(8, (int) $size)),
                'color' => preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $color) ? $color : '#1e293b',
                'align' => in_array($align, $aligns, true) ? $align : 'center',
                'font' => in_array($font, $fonts, true) ? $font : 'lato',
                'weight' => in_array($weight, $weights, true) ? $weight : 'bold',
                'enabled' => (bool) $enabled,
            ];
        }

        return $clean;
    }
}
