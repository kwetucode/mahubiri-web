<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\EmailVerificationWebController;
use App\Http\Controllers\Web\PasswordResetWebController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy.policy');

Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms.service');

//load admin routes
require __DIR__.'/admin.php';

//load test routes (remove in production)
require __DIR__.'/test.php';

