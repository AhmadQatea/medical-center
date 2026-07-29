<?php

namespace App\Http\Controllers\Doctor;

use App\Actions\AppointmentType\CreateAppointmentTypeAction;
use App\Actions\AppointmentType\DeleteAppointmentTypeAction;
use App\Actions\AppointmentType\ReorderAppointmentTypesAction;
use App\Actions\AppointmentType\ToggleAppointmentTypeAction;
use App\Actions\AppointmentType\UpdateAppointmentTypeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\ReorderAppointmentTypesRequest;
use App\Http\Requests\Doctor\StoreAppointmentTypeRequest;
use App\Http\Requests\Doctor\UpdateAppointmentTypeRequest;
use App\Models\AppointmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentTypeController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = filled($validated['search'] ?? null)
            ? trim((string) $validated['search'])
            : '';

        $types = $request->user()
            ->appointmentTypes()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->ordered()
            ->withCount('appointments')
            ->get();

        return view('doctor.appointment-types.index', [
            'types' => $types,
        ]);
    }

    public function create(): View
    {
        return view('doctor.appointment-types.create');
    }

    public function store(
        StoreAppointmentTypeRequest $request,
        CreateAppointmentTypeAction $createAppointmentType,
    ): RedirectResponse {
        $createAppointmentType->handle($request->user(), $request->typeData());

        return redirect()
            ->route('doctor.appointment-types.index')
            ->with('success', 'تم إنشاء نوع الموعد بنجاح.');
    }

    public function edit(AppointmentType $appointmentType): View
    {
        return view('doctor.appointment-types.edit', [
            'type' => $appointmentType,
        ]);
    }

    public function update(
        UpdateAppointmentTypeRequest $request,
        AppointmentType $appointmentType,
        UpdateAppointmentTypeAction $updateAppointmentType,
    ): RedirectResponse {
        $updateAppointmentType->handle($appointmentType, $request->typeData());

        return redirect()
            ->route('doctor.appointment-types.index')
            ->with('success', 'تم تحديث نوع الموعد بنجاح.');
    }

    public function destroy(
        AppointmentType $appointmentType,
        DeleteAppointmentTypeAction $deleteAppointmentType,
    ): RedirectResponse {
        $deleteAppointmentType->handle($appointmentType);

        return redirect()
            ->route('doctor.appointment-types.index')
            ->with('success', 'تم حذف نوع الموعد بنجاح.');
    }

    public function toggle(
        AppointmentType $appointmentType,
        ToggleAppointmentTypeAction $toggleAppointmentType,
    ): RedirectResponse {
        $type = $toggleAppointmentType->handle($appointmentType);

        $message = $type->is_active
            ? 'تم تفعيل نوع الموعد.'
            : 'تم إيقاف نوع الموعد.';

        return redirect()
            ->route('doctor.appointment-types.index')
            ->with('success', $message);
    }

    public function reorder(
        ReorderAppointmentTypesRequest $request,
        ReorderAppointmentTypesAction $reorderAppointmentTypes,
    ): RedirectResponse {
        $reorderAppointmentTypes->handle($request->user(), $request->orderedIds());

        return redirect()
            ->route('doctor.appointment-types.index')
            ->with('success', 'تم تحديث ترتيب أنواع المواعيد.');
    }
}
