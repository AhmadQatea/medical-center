<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\AdminBookingContextService;
use App\Services\TimelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function __construct(
        private TimelineService $timeline,
        private AdminBookingContextService $context,
    ) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        $bookingContext = $this->context->resolve(
            $request->user(),
            $request->integer('clinic_id') ?: null,
            $request->integer('doctor_id') ?: null,
        );

        if ($request->user()->isAdmin()
            && $bookingContext['auto_selected_doctor']
            && $bookingContext['doctor'] !== null
            && ! $request->filled('doctor_id')) {
            return redirect()->route('doctor.timeline.index', $this->context->queryParams(
                $bookingContext['clinic'],
                $bookingContext['doctor'],
            ));
        }

        $entries = collect();

        if ($bookingContext['state'] === AdminBookingContextService::STATE_READY
            && $bookingContext['doctor'] !== null) {
            $entries = $this->timeline->forToday($bookingContext['doctor']);
        }

        return view('doctor.timeline.index', [
            'bookingContext' => $bookingContext,
            'entries' => $entries,
            'todayLabel' => now()->locale('ar')->translatedFormat('l j F Y'),
        ]);
    }
}
