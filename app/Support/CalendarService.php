<?php

namespace App\Support;

use App\Models\Borrowing;
use App\Models\Event;
use App\Models\HealthCheckup;
use App\Models\ResearchProposal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    public function getEvents(Carbon $start, Carbon $end, ?User $user = null): Collection
    {
        $events = collect();

        $events = $events->merge($this->getBorrowingEvents($start, $end, $user));
        $events = $events->merge($this->getHealthCheckupEvents($start, $end, $user));
        $events = $events->merge($this->getResearchEvents($start, $end, $user));
        $events = $events->merge($this->getEventEvents($start, $end));

        return $events->sortBy('start')->values();
    }

    public function getBorrowingEvents(Carbon $start, Carbon $end, ?User $user = null): Collection
    {
        $query = Borrowing::query()
            ->whereIn('status', [
                Borrowing::STATUS_APPROVED,
                Borrowing::STATUS_BORROWED,
            ])
            ->where('borrow_date', '<=', $end)
            ->where('return_date', '>=', $start)
            ->with('user');

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->get()->map(function ($borrowing) {
            return [
                'id' => $borrowing->id,
                'title' => "Peminjaman: {$borrowing->code}",
                'start' => $borrowing->borrow_date->toDateString(),
                'end' => $borrowing->return_date->addDay()->toDateString(),
                'color' => $this->getStatusColor($borrowing->status, 'borrowing'),
                'borderColor' => $this->getStatusBorderColor($borrowing->status, 'borrowing'),
                'textColor' => '#1e293b',
                'extendedProps' => [
                    'type' => 'borrowing',
                    'code' => $borrowing->code,
                    'status' => $borrowing->status,
                    'status_label' => Borrowing::statusLabel($borrowing->status),
                    'user' => $borrowing->user?->name,
                    'borrow_date' => $borrowing->borrow_date->toDateString(),
                    'return_date' => $borrowing->return_date->toDateString(),
                    'duration_days' => $borrowing->duration_days,
                    'url' => route('borrowings.show', $borrowing),
                ],
            ];
        });
    }

    public function getHealthCheckupEvents(Carbon $start, Carbon $end, ?User $user = null): Collection
    {
        $query = HealthCheckup::query()
            ->whereIn('status', [
                HealthCheckup::STATUS_PENDING,
                HealthCheckup::STATUS_APPROVED,
            ])
            ->where('booking_date', '>=', $start->toDateString())
            ->where('booking_date', '<=', $end->toDateString())
            ->with(['user', 'type']);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->get()->map(function ($checkup) {
            return [
                'id' => $checkup->id,
                'title' => "MCU: {$checkup->code}",
                'start' => $checkup->booking_date->toDateString(),
                'end' => $checkup->booking_date->addDay()->toDateString(),
                'color' => $this->getStatusColor($checkup->status, 'health_checkup'),
                'borderColor' => $this->getStatusBorderColor($checkup->status, 'health_checkup'),
                'textColor' => '#1e293b',
                'extendedProps' => [
                    'type' => 'health_checkup',
                    'code' => $checkup->code,
                    'status' => $checkup->status,
                    'status_label' => HealthCheckup::statusLabel($checkup->status),
                    'user' => $checkup->user?->name,
                    'queue_number' => $checkup->queue_label,
                    'type_name' => $checkup->type?->name,
                    'url' => route('health-checkups.show', $checkup),
                ],
            ];
        });
    }

    public function getResearchEvents(Carbon $start, Carbon $end, ?User $user = null): Collection
    {
        $query = ResearchProposal::query()
            ->whereIn('status', [
                ResearchProposal::STATUS_APPROVED,
                ResearchProposal::STATUS_ONGOING,
            ])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->with(['user', 'laboran', 'laboratorium']);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->get()->map(function ($proposal) {
            return [
                'id' => $proposal->id,
                'title' => "Riset: {$proposal->title}",
                'start' => $proposal->start_date->toDateString(),
                'end' => $proposal->end_date->addDay()->toDateString(),
                'color' => $this->getStatusColor($proposal->status, 'research'),
                'borderColor' => $this->getStatusBorderColor($proposal->status, 'research'),
                'textColor' => '#1e293b',
                'extendedProps' => [
                    'type' => 'research',
                    'code' => $proposal->code,
                    'status' => $proposal->status,
                    'status_label' => ResearchProposal::statusLabel($proposal->status),
                    'user' => $proposal->user?->name,
                    'title' => $proposal->title,
                    'lab' => $proposal->laboratorium?->name,
                    'laboran' => $proposal->laboran?->name,
                    'start_date' => $proposal->start_date->toDateString(),
                    'end_date' => $proposal->end_date->toDateString(),
                    'duration_days' => $proposal->duration_days,
                    'url' => route('research.show', $proposal),
                ],
            ];
        });
    }

    public function getEventEvents(Carbon $start, Carbon $end): Collection
    {
        return Event::query()
            ->whereIn('status', [Event::STATUS_ACTIVE])
            ->where('starts_at', '<=', $end)
            ->where('ends_at', '>=', $start)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => "Event: {$event->title}",
                    'start' => $event->starts_at->toDateTimeString(),
                    'end' => $event->ends_at->toDateTimeString(),
                    'color' => $this->getStatusColor($event->status, 'event'),
                    'borderColor' => $this->getStatusBorderColor($event->status, 'event'),
                    'textColor' => '#1e293b',
                    'extendedProps' => [
                        'type' => 'event',
                        'code' => $event->code,
                        'status' => $event->status,
                        'status_label' => Event::statusLabel($event->status),
                        'mode' => $event->mode_label,
                        'location' => $event->location,
                        'url' => route('events.show', $event),
                    ],
                ];
            });
    }

    private function getStatusColor(string $status, string $module): string
    {
        return match ($module) {
            'borrowing' => match ($status) {
                'approved' => '#dbeafe',
                'borrowed' => '#fef3c7',
                default => '#f1f5f9',
            },
            'health_checkup' => match ($status) {
                'pending' => '#fef3c7',
                'approved' => '#d1fae5',
                default => '#f1f5f9',
            },
            'research' => match ($status) {
                'approved' => '#e9d5ff',
                'ongoing' => '#c4b5fd',
                default => '#f1f5f9',
            },
            'event' => match ($status) {
                'active' => '#fed7aa',
                default => '#f1f5f9',
            },
            default => '#f1f5f9',
        };
    }

    private function getStatusBorderColor(string $status, string $module): string
    {
        return match ($module) {
            'borrowing' => match ($status) {
                'approved' => '#3b82f6',
                'borrowed' => '#f59e0b',
                default => '#94a3b8',
            },
            'health_checkup' => match ($status) {
                'pending' => '#f59e0b',
                'approved' => '#10b981',
                default => '#94a3b8',
            },
            'research' => match ($status) {
                'approved' => '#8b5cf6',
                'ongoing' => '#7c3aed',
                default => '#94a3b8',
            },
            'event' => match ($status) {
                'active' => '#f97316',
                default => '#94a3b8',
            },
            default => '#94a3b8',
        };
    }

    public function exportToIcs(User $user): string
    {
        $start = now()->startOfMonth()->subMonth();
        $end = now()->endOfMonth()->addMonths(3);
        $events = $this->getEvents($start, $end, $user);

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//MarketLabs//Calendar//ID\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:MarketLabs\r\n";
        $ics .= "X-WR-TIMEZONE:Asia/Jakarta\r\n";

        foreach ($events as $event) {
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= 'DTSTART;VALUE=DATE:'.Carbon::parse($event['start'])->format('Ymd')."\r\n";
            $ics .= 'DTEND;VALUE=DATE:'.Carbon::parse($event['end'])->format('Ymd')."\r\n";
            $ics .= 'SUMMARY:'.$this->escapeIcsText($event['title'])."\r\n";
            $ics .= 'DESCRIPTION:'.$this->escapeIcsText($event['extendedProps']['status_label'] ?? '')."\r\n";
            $ics .= 'UID:'.$event['id']."@marketlabs\r\n";
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    private function escapeIcsText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace("\n", '\\n', $text);

        return $text;
    }
}
