<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\TimelineService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function __construct(
        private TimelineService $timeline,
    ) {}

    public function __invoke(Request $request): View
    {
        $entries = $this->timeline->forToday($request->user());

        return view('doctor.timeline.index', [
            'entries' => $entries,
            'todayLabel' => now()->locale('ar')->translatedFormat('l j F Y'),
        ]);
    }
}
