<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use App\Models\Clinic;
use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\DoctorManagementService;
use App\Support\BookingCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorManagementController extends Controller
{
    public function __construct(
        private ClinicSettingsService $clinicSettings,
        private DoctorManagementService $doctors,
    ) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'clinic_id' => ['nullable', 'integer', 'exists:clinics,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $doctors = User::query()
            ->where('role', UserRole::Doctor)
            ->when(filled($validated['clinic_id'] ?? null), fn ($query) => $query->where('clinic_id', $validated['clinic_id']))
            ->when(filled($validated['search'] ?? null), function ($query) use ($validated): void {
                $search = trim((string) $validated['search']);
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('specialty', 'like', "%{$search}%");
                });
            })
            ->with('clinic')
            ->withCount('appointments')
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(15);

        return view('doctor.doctors.index', [
            'doctors' => $doctors,
            'clinics' => Clinic::query()->ordered()->get(),
            'canDeleteDoctor' => fn (User $doctor): bool => $this->doctors->canDelete($doctor),
        ]);
    }

    public function create(): View
    {
        return view('doctor.doctors.create', [
            'clinics' => Clinic::query()->ordered()->get(),
        ]);
    }

    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        $doctor = $this->doctors->create($request->doctorData());

        $this->clinicSettings->get($doctor);

        return redirect()
            ->route('doctor.doctors.index')
            ->with('success', 'تم إنشاء الطبيب بنجاح.');
    }

    public function edit(User $doctor): View
    {
        abort_unless($doctor->isDoctor(), 404);

        return view('doctor.doctors.edit', [
            'doctor' => $doctor->loadCount('appointments'),
            'clinics' => Clinic::query()->ordered()->get(),
            'canDelete' => $this->doctors->canDelete($doctor),
        ]);
    }

    public function update(UpdateDoctorRequest $request, User $doctor): RedirectResponse
    {
        abort_unless($doctor->isDoctor(), 404);

        $this->doctors->update($doctor, $request->doctorData());

        return redirect()
            ->route('doctor.doctors.index')
            ->with('success', 'تم تحديث بيانات الطبيب بنجاح.');
    }

    public function destroy(User $doctor): RedirectResponse
    {
        abort_unless($doctor->isDoctor(), 404);

        $this->doctors->deleteIfAllowed($doctor);

        return redirect()
            ->route('doctor.doctors.index')
            ->with('success', 'تم حذف الطبيب بنجاح.');
    }

    public function toggle(User $doctor): RedirectResponse
    {
        abort_unless($doctor->isDoctor(), 404);

        $doctor->is_active = ! $doctor->is_active;
        $doctor->save();

        BookingCache::forgetActiveClinics();

        $message = $doctor->is_active
            ? 'تم تفعيل الطبيب.'
            : 'تم إيقاف الطبيب.';

        return redirect()
            ->route('doctor.doctors.index')
            ->with('success', $message);
    }
}
