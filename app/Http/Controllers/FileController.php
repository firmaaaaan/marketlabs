<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\EventRegistration;
use App\Models\HealthCheckup;
use App\Models\ResearchProposal;
use App\Models\SampleTest;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Unduh dokumen pendukung peminjaman (pemilik atau admin).
     */
    public function borrowingDocument(Borrowing $borrowing)
    {
        abort_unless($borrowing->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        return $this->download($borrowing->document_path);
    }

    /**
     * Unduh dokumen proposal riset (pemilik, laboran yang ditugaskan, atau admin).
     */
    public function researchDocument(ResearchProposal $proposal, string $type)
    {
        abort_unless(in_array($type, ['document', 'letter', 'replacement'], true), 404);

        $path = match ($type) {
            'document' => $proposal->document_path,
            'letter' => $proposal->letter_path,
            'replacement' => $proposal->replacement_letter_path,
        };

        abort_unless($path, 404);

        $isOwner = $proposal->user_id === auth()->id();
        $isLaboran = $proposal->laboran_id === auth()->id();

        abort_unless($isOwner || $isLaboran || auth()->user()->isAdmin(), 403);

        return $this->download($path);
    }

    /**
     * Unduh dokumen hasil pengujian sampel (pemilik, laboran, atau admin).
     */
    public function sampleTestResult(SampleTest $test)
    {
        abort_unless($test->result_file, 404);
        abort_unless(
            $test->user_id === auth()->id()
            || $test->laboran_id === auth()->id()
            || auth()->user()->isAdmin(),
            403
        );

        return $this->download($test->result_file);
    }

    /**
     * Unduh dokumen hasil pemeriksaan kesehatan (pemilik, pemeriksa, atau admin).
     */
    public function healthCheckupResult(HealthCheckup $checkup)
    {
        abort_unless($checkup->result_file, 404);
        abort_unless(
            $checkup->user_id === auth()->id()
            || $checkup->examiner_id === auth()->id()
            || auth()->user()->isAdmin(),
            403
        );

        return $this->download($checkup->result_file);
    }

    /**
     * Stream file jawaban registrasi event (bukti transfer, dll).
     * Hanya melayani jawaban berjenis file pada formulir event yang bersangkutan.
     */
    public function eventAnswerFile(EventRegistration $registration, string $key)
    {
        abort_unless(
            auth()->user()?->isAdmin()
            || $registration->user_id === auth()->id()
            || $registration->registered_by === auth()->id(),
            403
        );

        $field = collect($registration->event?->form_fields ?? [])
            ->first(fn ($f) => ($f['key'] ?? null) === $key && ($f['type'] ?? null) === 'file');

        abort_unless($field, 404);

        $path = $registration->answers[$key] ?? null;

        abort_unless($path && str_starts_with($path, 'events-answers/'), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        $mime = Storage::disk('public')->mimeType($path);
        $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($path));
        $content = Storage::disk('public')->get($path);

        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$name.'"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Stream file inline di browser (default) atau download (?download=1).
     */
    protected function download(?string $path)
    {
        abort_unless($path, 404);

        // Tentukan disk yang menyimpan file.
        if (Storage::disk('local')->exists($path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = 'public';
        } else {
            abort(404);
        }

        $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($path));
        $mime = Storage::disk($disk)->mimeType($path);
        $stream = Storage::disk($disk)->readStream($path);

        // ?download=1 → paksa download.
        if (request()->boolean('download')) {
            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'attachment; filename="'.$name.'"',
            ]);
        }

        // Default: stream inline di browser.
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
