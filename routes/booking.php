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

        Route::post('/', [PublicBookingController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('store');

        Route::get('/success', [PublicBookingController::class, 'success'])->name('success');
    });
