<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\ServiceSchedule;
use Illuminate\Support\Carbon;

class LabScheduleController extends Controller
{
    public function index()
    {
        $operationalDays = ServiceSchedule::days();
        $dayNames = ServiceSchedule::DAY_NAMES;
        $openTime = ServiceSchedule::openTime();
        $closeTime = ServiceSchedule::closeTime();
        $scheduleEnabled = ServiceSchedule::enabled();

        $schedule = collect($dayNames)->map(function ($name, $day) use ($operationalDays) {
            return [
                'name' => $name,
                'is_open' => in_array($day, $operationalDays),
            ];
        })->values();

        $upcomingEvents = Event::where('status', Event::STATUS_ACTIVE)
            ->where('starts_at', '>=', Carbon::now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get()
            ->map(fn ($event) => [
                'title' => $event->title,
                'starts_at' => $event->starts_at,
                'ends_at' => $event->ends_at,
                'location' => $event->location,
                'mode' => $event->mode_label,
                'url' => route('events.show', $event),
            ]);

        return view('lab-schedule.index', compact(
            'schedule',
            'openTime',
            'closeTime',
            'scheduleEnabled',
            'upcomingEvents',
        ));
    }
}
