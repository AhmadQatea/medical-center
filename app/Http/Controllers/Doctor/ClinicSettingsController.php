<?php

namespace App\Http\Controllers\Doctor;

use App\Actions\ClinicSettings\UpdateClinicSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\UpdateClinicSettingsRequest;
use App\Services\ClinicSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicSettingsController extends Controller
{
    public function __construct(
        private ClinicSettingsService $clinicSettings,
    ) {}

    /**
     * Show the clinic settings form.
     */
    public function edit(Request $request): View
    {
        $settings = $this->clinicSettings->get($request->user());

        return view('doctor.settings.index', [
            'settings' => $settings,
            'doctor' => $request->user(),
        ]);
    }

    /**
     * Persist clinic identity and WhatsApp settings.
     */
    public function update(
        UpdateClinicSettingsRequest $request,
        UpdateClinicSettingsAction $updateClinicSettings,
    ): RedirectResponse {
        $updateClinicSettings->handle($request->user(), $request->clinicData());

        return redirect()
            ->route('doctor.settings.index')
            ->with('success', 'تم حفظ إعدادات الحجز العام بنجاح.');
    }
}
