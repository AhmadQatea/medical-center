<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| App-level entry points only.
| Domain routes live in booking.php, doctor.php, and auth.php
| (registered from bootstrap/app.php).
|
*/

Route::redirect('/', '/book')->name('home');

Route::redirect('/dashboard', '/doctor/dashboard')
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Account profile (Breeze compatibility)
|--------------------------------------------------------------------------
|
| UI lives at doctor.profile.*. These routes keep PATCH/DELETE account
| updates working until Doctor\ProfileController Actions are implemented.
|
*/

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
