<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\EmailVerificationWebController;
use App\Http\Controllers\Web\PasswordResetWebController;

Route::get('/', function () {
    return view('welcome')->with('version', app()
        ->version());
})->name('welcome');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy.policy');

Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms.service');
