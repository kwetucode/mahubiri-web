<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Church\ChurchController;
use App\Http\Controllers\Api\Church\UpdateLogoChurchController;
use App\Http\Controllers\Api\Church\ChurchStatisticsController;
use App\Http\Controllers\Api\Sermon\FavoriteSermonController;
use App\Http\Controllers\Api\Sermon\SermonController;
use App\Http\Controllers\Api\Sermon\SermonListController;
use App\Http\Controllers\Api\Sermon\SermonSearchController;
use App\Http\Controllers\Api\User\UserAvatarController;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Models\Sermon;

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
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);

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
    // User Profile Management
    Route::get('/profile', [UserProfileController::class, 'me']);
    Route::put('/profile/update', [UserProfileController::class, 'updateProfile']);
    Route::post('/logout', [UserProfileController::class, 'logout']);

    // Avatar Management
    Route::post('/avatar/update', [UserAvatarController::class, 'updateAvatar']);
    Route::delete('/avatar/delete', [UserAvatarController::class, 'removeAvatar']);

    // Email Verification Status (protected routes)
    Route::get('/email/verification-status', [EmailVerificationController::class, 'checkVerificationStatus']);

    // Social Account Management (protected routes)
    Route::post('/social/link', [SocialAuthController::class, 'linkSocialAccount']);
    Route::post('/social/unlink', [SocialAuthController::class, 'unlinkSocialAccount']);
    Route::get('/social/status', [SocialAuthController::class, 'getSocialAccountsStatus']);
});

//Churches routes group
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/churches', ChurchController::class);
    // Image management routes
    Route::post('/churches/{church}/logo', [UpdateLogoChurchController::class, 'updateLogo']);
    Route::delete('/churches/{church}/logo', [UpdateLogoChurchController::class, 'removeLogo']);

    // Church statistics routes
    Route::get('/churches/statistics/test', [ChurchStatisticsController::class, 'testStats']);
    Route::get('/churches/statistics/quick', [ChurchStatisticsController::class, 'getQuickStats']);
    Route::get('/churches/statistics/full', [ChurchStatisticsController::class, 'getChurchStatistics']);
});

//Sermons routes group
Route::middleware('auth:sanctum')->prefix('sermons')->group(function () {
    // Get recent sermons (must be before resource routes to avoid conflict)
    Route::get('/recent', [SermonListController::class, 'getRecentSermons']);

    // Get popular sermons based on popularity score
    Route::get('/popular', [SermonListController::class, 'getPopularSermons']);

    // Record sermon play/view
    Route::post('/{sermon}/play', [SermonListController::class, 'recordSermonPlay']);

    // Sermon search routes
    Route::get('/search', [SermonSearchController::class, 'search']);
    Route::post('/search/advanced', [SermonSearchController::class, 'advancedSearch']);
    Route::get('/search/suggestions', [SermonSearchController::class, 'suggestions']);

    // CategorySermon CRUD
    Route::apiResource('/categories', \App\Http\Controllers\Api\Sermon\CategorySermonController::class);

    Route::apiResource('/sermons', SermonController::class);

    // Sermon Favorites routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteSermonController::class, 'getFavorites']);
        Route::post('/{sermon}/add', [FavoriteSermonController::class, 'addFavorite']);
        Route::delete('/{sermon}/remove', [FavoriteSermonController::class, 'removeFavorite']);
        Route::post('/{sermon}/toggle', [FavoriteSermonController::class, 'toggleFavorite']);
        Route::get('/{sermon}/check', [FavoriteSermonController::class, 'isFavorite']);
    });
});
