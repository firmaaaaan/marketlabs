<?php

namespace App\Jobs;

use App\Models\Borrowing;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\HealthCheckup;
use App\Models\ResearchProposal;
use App\Models\SampleTest;
use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ZipDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public string $feature,
        public string $dateFrom,
        public string $dateTo,
        public string $userId,
    ) {}

    public function handle(): void
    {
        $meta = $this->getFeatures()[$this->feature];
        $model = $meta['model'];
        $dateColumn = $meta['date_column'];

        $records = $model::query()
            ->whereDate($dateColumn, '>=', $this->dateFrom)
            ->whereDate($dateColumn, '<=', $this->dateTo)
            ->latest($dateColumn)
            ->get();

        $zipFile = storage_path('app/zip-downloads/dokumen-'.$this->feature.'-'.now()->format('Ymd-His').'.zip');
        $dir = dirname($zipFile);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file ZIP.');
        }

        foreach ($records as $record) {
            $files = $this->getDocumentPaths($record, $this->feature);
            if ($files->isEmpty()) {
                continue;
            }

            $folderName = $this->sanitizeFolderName($record->{$meta['code_column']});

            foreach ($files as $label => $path) {
                $disk = $this->resolveDisk($path);
                if (! $disk || ! Storage::disk($disk)->exists($path)) {
                    continue;
                }

                $content = Storage::disk($disk)->get($path);
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $fileName = "{$label}.{$ext}";
                $zip->addFromString("{$folderName}/{$fileName}", $content);
            }
        }

        $zip->close();

        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new EventNotification(
                'ZIP Dokumen Tersedia',
                "File ZIP dokumen {$meta['label']} telah selesai dibuat. Silakan unduh dari halaman Download Dokumen.",
                url: null,
                notifyViaEmail: false,
            ));
        }
    }

    public function failed(\Throwable $exception): void
    {
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new EventNotification(
                'ZIP Dokumen Gagal',
                "Pembuatan ZIP dokumen gagal: {$exception->getMessage()}",
                notifyViaEmail: false,
            ));
        }
    }

    protected function getFeatures(): array
    {
        return [
            'borrowing' => [
                'label' => 'Peminjaman Alat',
                'date_column' => 'borrow_date',
                'model' => Borrowing::class,
                'code_column' => 'code',
            ],
            'research' => [
                'label' => 'Proposal Riset',
                'date_column' => 'start_date',
                'model' => ResearchProposal::class,
                'code_column' => 'code',
            ],
            'sample_test' => [
                'label' => 'Pengujian Sampel',
                'date_column' => 'created_at',
                'model' => SampleTest::class,
                'code_column' => 'code',
            ],
            'health_checkup' => [
                'label' => 'Pemeriksaan Kesehatan',
                'date_column' => 'booking_date',
                'model' => HealthCheckup::class,
                'code_column' => 'code',
            ],
            'event' => [
                'label' => 'Event',
                'date_column' => 'created_at',
                'model' => Event::class,
                'code_column' => 'slug',
            ],
        ];
    }

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

    protected function sanitizeFolderName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
    }
}
