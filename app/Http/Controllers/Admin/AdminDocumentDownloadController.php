<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ZipDocumentsJob;
use App\Models\Borrowing;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\HealthCheckup;
use App\Models\ResearchProposal;
use App\Models\SampleTest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminDocumentDownloadController extends Controller
{
    /**
     * Daftar fitur yang tersedia beserta metadata-nya.
     */
    protected function features(): array
    {
        return [
            'borrowing' => [
                'label' => 'Peminjaman Alat',
                'date_column' => 'borrow_date',
                'date_label' => 'Tanggal Pinjam',
                'model' => Borrowing::class,
                'code_column' => 'code',
                'name_from' => fn (Borrowing $b) => $b->user->name,
            ],
            'research' => [
                'label' => 'Proposal Riset',
                'date_column' => 'start_date',
                'date_label' => 'Tanggal Mulai',
                'model' => ResearchProposal::class,
                'code_column' => 'code',
                'name_from' => fn (ResearchProposal $r) => $r->user->name,
            ],
            'sample_test' => [
                'label' => 'Pengujian Sampel',
                'date_column' => 'created_at',
                'date_label' => 'Tanggal Dibuat',
                'model' => SampleTest::class,
                'code_column' => 'code',
                'name_from' => fn (SampleTest $s) => $s->user->name,
            ],
            'health_checkup' => [
                'label' => 'Pemeriksaan Kesehatan',
                'date_column' => 'booking_date',
                'date_label' => 'Tanggal Booking',
                'model' => HealthCheckup::class,
                'code_column' => 'code',
                'name_from' => fn (HealthCheckup $h) => $h->user->name,
            ],
            'event' => [
                'label' => 'Event',
                'date_column' => 'created_at',
                'date_label' => 'Tanggal Dibuat',
                'model' => Event::class,
                'code_column' => 'slug',
                'name_from' => fn (Event $e) => $e->title,
            ],
        ];
    }

    public function index()
    {
        $features = collect($this->features())->map(fn ($f) => $f['label']);

        return view('admin.document-downloads.index', compact('features'));
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'feature' => ['required', 'string', Rule::in(array_keys($this->features()))],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $feature = $validated['feature'];
        $meta = $this->features()[$feature];
        $model = $meta['model'];

        $dateColumn = $meta['date_column'];
        $query = $model::query()
            ->whereDate($dateColumn, '>=', $validated['date_from'])
            ->whereDate($dateColumn, '<=', $validated['date_to']);

        if ($feature === 'borrowing') {
            $query->with('user');
        } elseif ($feature === 'research') {
            $query->with('user');
        } elseif ($feature === 'sample_test') {
            $query->with('user');
        } elseif ($feature === 'health_checkup') {
            $query->with('user');
        } elseif ($feature === 'event') {
            $query->withCount('registrations');
        }

        $records = $query->latest($dateColumn)->get();

        $items = $records->map(function ($record) use ($feature, $meta) {
            $files = $this->getDocumentPaths($record, $feature);

            return [
                'id' => $record->getKey(),
                'code' => $record->{$meta['code_column']},
                'name' => $meta['name_from']($record),
                'date' => $record->{$meta['date_column']}?->format('d/m/Y') ?? '-',
                'file_count' => $files->count(),
                'files' => $files->values()->all(),
            ];
        })->filter(fn ($item) => $item['file_count'] > 0)->values();

        return response()->json([
            'items' => $items,
            'total_records' => $records->count(),
            'total_files' => $items->sum('file_count'),
        ]);
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'feature' => ['required', 'string', Rule::in(array_keys($this->features()))],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        ZipDocumentsJob::dispatch(
            $validated['feature'],
            $validated['date_from'],
            $validated['date_to'],
            auth()->id()
        );

        return back()->with('success', 'Pembuatan ZIP dokumen sedang diproses di queue. Anda akan mendapat notifikasi setelah selesai.');
    }

    /**
     * Ambil semua path dokumen dari record berdasarkan fitur.
     */
    protected function getDocumentPaths($record, string $feature): Collection
    {
        return match ($feature) {
            'borrowing' => collect([
                'Dokumen' => $record->document_path,
            ])->filter(),
            'research' => collect([
                'Dokumen Proposal' => $record->document_path,
                'Surat Pengajuan' => $record->letter_path,
                'Surat Pengganti' => $record->replacement_letter_path,
            ])->filter(),
            'sample_test' => collect([
                'File Hasil' => $record->result_file,
            ])->filter(),
            'health_checkup' => collect([
                'File Hasil' => $record->result_file,
            ])->filter(),
            'event' => collect([
                'Gambar' => $record->image,
                'Poster' => $record->poster,
            ])->filter()
                ->merge(
                    EventRegistration::where('event_id', $record->id)
                        ->get()
                        ->flatMap(function (EventRegistration $reg) use ($record) {
                            $files = [];

                            if ($reg->certificate_path) {
                                $files["Sertifikat/{$reg->id}"] = $reg->certificate_path;
                            }
                            if ($reg->certificate_back_path) {
                                $files["Sertifikat Belakang/{$reg->id}"] = $reg->certificate_back_path;
                            }

                            $fileFields = collect($record->form_fields ?? [])
                                ->where('type', 'file')
                                ->pluck('key')
                                ->all();

                            foreach ($fileFields as $fieldKey) {
                                $path = $reg->answers[$fieldKey] ?? null;
                                if ($path && is_string($path) && str_starts_with($path, 'events-answers/')) {
                                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                                    $files["Jawaban/{$reg->id}/{$fieldKey}.{$ext}"] = $path;
                                }
                            }

                            return $files;
                        })
                ),
            default => collect(),
        };
    }

    /**
     * Tentukan disk penyimpanan file.
     */
    protected function resolveDisk(string $path): ?string
    {
        if (Storage::disk('local')->exists($path)) {
            return 'local';
        }

        if (Storage::disk('public')->exists($path)) {
            return 'public';
        }

        return null;
    }

    /**
     * Sanitasi nama folder agar aman untuk ZIP.
     */
    protected function sanitizeFolderName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
    }
}
