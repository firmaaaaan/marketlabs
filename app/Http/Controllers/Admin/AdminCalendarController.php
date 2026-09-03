<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CalendarService;
use Illuminate\Http\Response;

class AdminCalendarController extends Controller
{
    public function __construct(
        private readonly CalendarService $calendarService,
    ) {}

    public function index()
    {
        return view('admin.calendar.index');
    }

    public function export(): Response
    {
        $ics = $this->calendarService->exportToIcs(auth()->user());

        return response($ics, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="kalender-marketlabs-admin.ics"');
    }
}
