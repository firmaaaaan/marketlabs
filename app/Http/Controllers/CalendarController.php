<?php

namespace App\Http\Controllers;

use App\Support\CalendarService;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarService $calendarService,
    ) {}

    public function index()
    {
        return view('calendar.index');
    }

    public function export(): Response
    {
        $ics = $this->calendarService->exportToIcs(auth()->user());

        return response($ics, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="kalender-marketlabs.ics"');
    }
}
