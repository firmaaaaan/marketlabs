<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarApiController extends Controller
{
    public function __construct(
        private readonly CalendarService $calendarService,
    ) {}

    public function events(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->input('start', now()->startOfMonth()));
        $end = Carbon::parse($request->input('end', now()->endOfMonth()));

        $user = $request->boolean('my_only') ? auth()->user() : null;

        $events = $this->calendarService->getEvents($start, $end, $user);

        return response()->json($events);
    }
}
