<?php

namespace App\Jobs;

use App\Models\EventRegistration;
use App\Notifications\EventNotification;
use App\Support\CertificateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public EventRegistration $registration,
    ) {}

    public function handle(): void
    {
        $registration = $this->registration->fresh(['event', 'user']);

        if (! $registration || ! $registration->attended_at || $registration->certificate_number) {
            return;
        }

        $event = $registration->event;

        if (! $event->certificate_ready) {
            return;
        }

        $registration->certificate_status = 'processing';
        $registration->save();

        $paths = CertificateRenderer::render($event, $registration);

        $certificateNumber = $this->generateCertificateNumber();

        $registration->certificate_number = $certificateNumber;
        $registration->certificate_path = $paths['front'];
        $registration->certificate_back_path = $paths['back'] ?? null;
        $registration->certificate_generated_at = now();
        $registration->certificate_status = 'completed';
        $registration->certificate_error = null;
        $registration->save();

        $registration->user->notify(new EventNotification(
            'Sertifikat Tersedia',
            "Sertifikat untuk event '{$event->title}' telah tersedia. Silakan unduh sertifikat Anda.",
            route('events.certificate', $registration),
            notifyViaEmail: true,
        ));

        $event->increment('certificate_batch_done');

        if ($event->certificate_batch_done >= $event->certificate_batch_total) {
            $event->update(['certificate_batch_status' => 'completed']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $registration = $this->registration;

        if (! $registration) {
            return;
        }

        $registration->update([
            'certificate_status' => 'failed',
            'certificate_error' => $exception->getMessage(),
        ]);
    }

    protected function generateCertificateNumber(): string
    {
        return DB::transaction(function () {
            $year = date('Y');
            $prefix = 'CERT-'.$year.'-';

            $maxNumber = EventRegistration::where('certificate_number', 'like', $prefix.'%')
                ->pluck('certificate_number')
                ->map(fn ($num) => (int) str_replace($prefix, '', $num))
                ->max() ?? 0;

            return $prefix.str_pad((string) ($maxNumber + 1), 4, '0', STR_PAD_LEFT);
        });
    }
}
