<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCertificatesBatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(
        public Event $event,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $registrations = $this->event->registrations()
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->whereNotNull('attended_at')
            ->whereNull('certificate_number')
            ->with('user')
            ->get();

        if ($registrations->isEmpty()) {
            $this->event->update([
                'certificate_batch_status' => 'completed',
                'certificate_batch_total' => 0,
                'certificate_batch_done' => 0,
            ]);

            return;
        }

        $this->event->update([
            'certificate_batch_status' => 'processing',
            'certificate_batch_total' => $registrations->count(),
            'certificate_batch_done' => 0,
        ]);

        foreach (array_chunk($registrations->all(), 10) as $chunk) {
            foreach ($chunk as $registration) {
                $registration->update(['certificate_status' => 'pending']);
                GenerateCertificateJob::dispatch($registration);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->event->update([
            'certificate_batch_status' => 'failed',
        ]);
    }
}
