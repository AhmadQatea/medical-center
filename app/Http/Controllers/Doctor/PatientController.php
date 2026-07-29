<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\ListPatientsRequest;
use App\Services\PatientService;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(
        private PatientService $patients,
    ) {}

    public function index(ListPatientsRequest $request): View
    {
        $patients = $this->patients->listForDoctor(
            $request->user(),
            $request->search(),
        );

        return view('doctor.patients.index', compact('patients'));
    }
}
