<?php

use App\Http\Controllers\Booking\PublicBookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Booking Routes
|--------------------------------------------------------------------------
|
| Guest patient booking flow. No authentication.
| Prefix: /book  ·  Names: booking.*
|
*/

Route::prefix('book')
    ->name('booking.')
    ->group(function (): void {
        Route::get('/', [PublicBookingController::class, 'index'])->name('index');

        Route::get('/clinic/{clinic:slug}', [PublicBookingController::class, 'clinic'])->name('clinic');

        Route::get('/clinic/{clinic:slug}/doctor/{doctor}', [PublicBookingController::class, 'book'])
            ->name('book');

        Route::get('/doctor/{doctor}', [PublicBookingController::class, 'doctorEntry'])->name('doctor');

        Route::post('/', [PublicBookingController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('store');

        Route::get('/success', [PublicBookingController::class, 'success'])->name('success');
    });
