<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\EmailVerificationWebController;
use App\Http\Controllers\Web\PasswordResetWebController;

Route::get('/', function () {
    return view('welcome')->with('version', app()
        ->version());
})->name('welcome');

// Email Verification - Web route for email link clicks
Route::get('/email/verify/{id}/{hash}', [EmailVerificationWebController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Password Reset - Web route for email link clicks
Route::get('/reset-password', [PasswordResetWebController::class, 'showResetForm'])
    ->name('password.reset');
