<?php

use App\Http\Controllers\Doctor\BookingController;
use App\Http\Controllers\Doctor\ClinicController;
use App\Http\Controllers\Doctor\ClinicSettingsController;
use App\Http\Controllers\Doctor\DashboardController;
use App\Http\Controllers\Doctor\DoctorManagementController;
use App\Http\Controllers\Doctor\PatientController;
use App\Http\Controllers\Doctor\ProfileController;
use App\Http\Controllers\Doctor\ScheduleController;
use App\Http\Controllers\Doctor\TimelineController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Doctor Routes — Authenticated Clinic Dashboard
|--------------------------------------------------------------------------
|
| Prefix: /doctor  ·  Names: doctor.*  ·  Middleware: auth
| Controllers stay thin; business logic belongs in Actions/Services.
|
*/

Route::middleware(['auth', 'throttle:120,1'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function (): void {

        /*
        | Dashboard — today's overview
        */
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::middleware('admin')->group(function (): void {
            Route::patch('clinics/{clinic}/toggle', [ClinicController::class, 'toggle'])->name('clinics.toggle');
            Route::resource('clinics', ClinicController::class)->except(['show']);

            Route::patch('doctors/{doctor}/toggle', [DoctorManagementController::class, 'toggle'])->name('doctors.toggle');
            Route::resource('doctors', DoctorManagementController::class)->except(['show']);
        });

        /*
        | Bookings — instant form + appointment resource
        */
        Route::get('bookings/instant', [BookingController::class, 'instant'])
            ->name('bookings.instant');

        Route::resource('bookings', BookingController::class)
            ->except(['create'])
            ->parameters(['bookings' => 'appointment'])
            ->names('bookings');

        Route::post('bookings/{appointment}/confirm', [BookingController::class, 'confirm'])
            ->name('bookings.confirm');

        Route::patch('bookings/{appointment}/status', [BookingController::class, 'updateStatus'])
            ->name('bookings.status');

        /*
        | Timeline — today's appointments by time
        */
        Route::get('timeline', TimelineController::class)->name('timeline.index');

        /*
        | Schedule — working hours, lunch, holidays
        */
        Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::put('schedule', [ScheduleController::class, 'update'])->name('schedule.update');
        Route::post('schedule/holidays', [ScheduleController::class, 'storeHoliday'])->name('schedule.holidays.store');
        Route::delete('schedule/holidays/{holiday}', [ScheduleController::class, 'destroyHoliday'])->name('schedule.holidays.destroy');

        /*
        | Patients — read-only directory (CRUD not implemented yet)
        */
        Route::get('patients', [PatientController::class, 'index'])->name('patients.index');

        /*
        | Clinic settings — public brand & WhatsApp
        */
        Route::get('settings', [ClinicSettingsController::class, 'edit'])->name('settings.index');
        Route::put('settings', [ClinicSettingsController::class, 'update'])->name('settings.update');

        /*
        | Profile — doctor account UI (updates via Breeze /profile routes)
        */
        Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    });
