<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Church\ChurchController;
use App\Http\Controllers\Api\Sermon\SermonController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    // Registration & Login
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);

    // Password Reset with Verification Code
    Route::post('/password/send-code', [PasswordResetController::class, 'sendResetCode']);
    Route::post('/password/verify-code', [PasswordResetController::class, 'verifyCode']);
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])
        ->name('password.reset');

    // Email Verification with Code
    Route::post('/email/send-code', [EmailVerificationController::class, 'sendVerificationCode'])
        ->middleware(['auth:sanctum', 'throttle:6,1']);
    Route::post('/email/verify-code', [EmailVerificationController::class, 'verifyEmailWithCode'])
        ->middleware(['auth:sanctum']);

    // Social Authentication (for Flutter app)
    Route::post('/social/login', [SocialAuthController::class, 'socialLogin'])
        ->name('social.login');
});

// Protected authentication routes
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/profile', [LoginController::class, 'me']);

    // Email Verification Status (protected routes)
    Route::get('/email/verification-status', [EmailVerificationController::class, 'checkVerificationStatus']);

    // Social Account Management (protected routes)
    Route::post('/social/link', [SocialAuthController::class, 'linkSocialAccount']);
    Route::post('/social/unlink', [SocialAuthController::class, 'unlinkSocialAccount']);
    Route::get('/social/status', [SocialAuthController::class, 'getSocialAccountsStatus']);
});

//Churches routes group
Route::middleware('auth:sanctum')->prefix('churches')->group(function () {
    Route::get('/my-church', [ChurchController::class, 'checkUserChurch']);
    Route::apiResource('/', ChurchController::class, [
        'parameters' => ['' => 'church'],
        'names' => [
            'index' => 'churches.index',
            'store' => 'churches.store',
            'show' => 'churches.show',
            'update' => 'churches.update',
            'destroy' => 'churches.destroy',
        ],
    ]);

    // Image management routes
    Route::patch('/{church}/logo', [ChurchController::class, 'updateLogo']);
    Route::delete('/{church}/logo', [ChurchController::class, 'removeLogo']);
});

//Sermons routes group
Route::middleware('auth:sanctum')->prefix('sermons')->group(function () {
    //Route::get('/my-church-sermons', [SermonController::class, 'myChurchSermons']);
    Route::apiResource('/', SermonController::class);
});
