<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the doctor's account profile page.
     */
    public function index(Request $request): View
    {
        return view('doctor.profile.index', [
            'user' => $request->user(),
        ]);
    }
}
