<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreClinicRequest;
use App\Http\Requests\Doctor\UpdateClinicRequest;
use App\Models\Clinic;
use App\Services\ClinicService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClinicController extends Controller
{
    public function __construct(private ClinicService $clinics) {}

    public function index(): View
    {
        return view('doctor.clinics.index', [
            'clinics' => $this->clinics->listAll(),
        ]);
    }

    public function create(): View
    {
        return view('doctor.clinics.create');
    }

    public function store(StoreClinicRequest $request): RedirectResponse
    {
        $this->clinics->create($request->clinicData());

        return redirect()
            ->route('doctor.clinics.index')
            ->with('success', 'تم إنشاء العيادة بنجاح.');
    }

    public function edit(Clinic $clinic): View
    {
        $clinic->loadCount(['doctors', 'appointments']);

        return view('doctor.clinics.edit', [
            'clinic' => $clinic,
            'canDelete' => $this->clinics->canDelete($clinic),
            'hasFutureAppointments' => $this->clinics->hasFutureAppointments($clinic),
        ]);
    }

    public function update(UpdateClinicRequest $request, Clinic $clinic): RedirectResponse
    {
        $this->clinics->update($clinic, $request->clinicData());

        return redirect()
            ->route('doctor.clinics.index')
            ->with('success', 'تم تحديث العيادة بنجاح.');
    }

    public function destroy(Clinic $clinic): RedirectResponse
    {
        $this->clinics->deleteIfAllowed($clinic);

        return redirect()
            ->route('doctor.clinics.index')
            ->with('success', 'تم حذف العيادة بنجاح.');
    }

    public function toggle(Clinic $clinic): RedirectResponse
    {
        $clinic = $this->clinics->toggle($clinic);

        $message = $clinic->is_active
            ? 'تم تفعيل العيادة.'
            : 'تم إيقاف العيادة.';

        return redirect()
            ->route('doctor.clinics.index')
            ->with('success', $message);
    }
}
