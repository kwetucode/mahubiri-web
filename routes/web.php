<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\EmailVerificationWebController;
use App\Http\Controllers\Web\PasswordResetWebController;
use App\Http\Controllers\Web\ContactController;
use App\Models\Sermon;
use App\Models\PreacherProfile;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'stats' => [
            'sermons' => Sermon::where('is_published', true)->count(),
            'preachers' => PreacherProfile::where('is_active', true)->count(),
            'languages' => 4,
        ],
    ]);
})->name('welcome');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

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

