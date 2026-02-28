<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RefreshTokenController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Church\ChurchController;
use App\Http\Controllers\Api\Church\ChurchListController;
use App\Http\Controllers\Api\Church\UpdateLogoChurchController;
use App\Http\Controllers\Api\Church\ChurchStatisticsController;
use App\Http\Controllers\Api\Sermon\FavoriteSermonController;
use App\Http\Controllers\Api\Sermon\SermonController;
use App\Http\Controllers\Api\Sermon\SermonListController;
use App\Http\Controllers\Api\Sermon\SermonSearchController;
use App\Http\Controllers\Api\User\UserAvatarController;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Http\Controllers\Api\User\UserSecurityController;
use App\Http\Controllers\Api\User\UserStatsController;
use App\Http\Controllers\Api\Notification\NotificationSettingsController;
use App\Http\Controllers\Api\Notification\FcmTokenController;
use App\Http\Controllers\Api\Preacher\DashboardPreacherController;
use App\Http\Controllers\Api\Preacher\PreacherProfileController;
use App\Http\Controllers\Api\Preacher\PreacherListController;
use App\Http\Controllers\Api\Sermon\CategorySermonController;
use App\Http\Controllers\Api\Donation\DonationController;
use App\Http\Controllers\Api\Admin\AdminStatsController;
use App\Http\Controllers\Api\Admin\AdminManagementController;
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
    // Token Management
    Route::post('/refresh-token', RefreshTokenController::class);

    // User Profile Management
    Route::get('/profile', [UserProfileController::class, 'me']);
    Route::put('/profile/update', [UserProfileController::class, 'updateProfile']);
    Route::post('/logout', [UserProfileController::class, 'logout']);

    // Avatar Management
    Route::post('/avatar/update', [UserAvatarController::class, 'updateAvatar']);
    Route::delete('/avatar/delete', [UserAvatarController::class, 'removeAvatar']);

    // User Statistics for Dashboard Widget
    Route::get('/stats', [UserStatsController::class, 'getUserStats']);
    Route::get('/stats/detailed', [UserStatsController::class, 'getDetailedStats']);

    // Email Verification Status (protected routes)
    Route::get('/email/verification-status', [EmailVerificationController::class, 'checkVerificationStatus']);

    // Social Account Management (protected routes)
    Route::post('/social/link', [SocialAuthController::class, 'linkSocialAccount']);
    Route::post('/social/unlink', [SocialAuthController::class, 'unlinkSocialAccount']);
    Route::get('/social/status', [SocialAuthController::class, 'getSocialAccountsStatus']);

    // Security Settings (Password & Email Change)
    Route::prefix('security')->group(function () {
        Route::get('/settings', [UserSecurityController::class, 'getSecuritySettings']);
        Route::post('/change-password', [UserSecurityController::class, 'changePassword']);
        Route::post('/request-email-change', [UserSecurityController::class, 'requestEmailChange']);
        Route::post('/verify-email-change', [UserSecurityController::class, 'verifyEmailChange']);
    });

    // Notification Settings Management
    Route::prefix('notification-settings')->group(function () {
        Route::get('/', [NotificationSettingsController::class, 'index']);
        Route::post('/', [NotificationSettingsController::class, 'store']);
        Route::patch('/', [NotificationSettingsController::class, 'update']);
    });

    // FCM Token Management
    Route::prefix('fcm-token')->group(function () {
        Route::get('/', [FcmTokenController::class, 'index']);
        Route::post('/', [FcmTokenController::class, 'store']);
        Route::delete('/', [FcmTokenController::class, 'destroy']);
    });
});

//Churches routes group
Route::middleware('auth:sanctum')->group(function () {
    // Church list routes (must be before apiResource routes)
    Route::get('/churches/list', [ChurchListController::class, 'index']);
    Route::get('/churches/list/country/{country}', [ChurchListController::class, 'byCountry']);
    Route::get('/churches/list/city/{city}', [ChurchListController::class, 'byCity']);

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
Route::middleware('auth:sanctum')->group(function () {
    // Get recent sermons (must be before resource routes to avoid conflict)
    Route::get('/sermons/recent', [SermonListController::class, 'getRecentSermons']);

    // Get popular sermons based on popularity score
    Route::get('/sermons/popular', [SermonListController::class, 'getPopularSermons']);

    // Get all sermons by category (paginated)
    Route::get('/sermons/category/{categoryId}', [SermonListController::class, 'getSermonsByCategory']);

    // Get all sermons by church (paginated)
    Route::get('/sermons/church/{churchId}', [SermonListController::class, 'getSermonsByChurch']);

    // Get sermon with related sermons from same category (paginated)
    Route::get('/sermons/{sermon}/related', [SermonListController::class, 'getSermonWithRelated']);

    // Record sermon play/view
    Route::post('/sermons/{sermon}/play', [SermonListController::class, 'recordSermonPlay']);

    // Sermon search routes
    Route::get('/sermons/search', [SermonSearchController::class, 'search']);
    Route::post('/sermons/search/advanced', [SermonSearchController::class, 'advancedSearch']);
    Route::get('/sermons/search/suggestions', [SermonSearchController::class, 'suggestions']);

    // CategorySermon CRUD
    Route::apiResource('/sermons/categories', CategorySermonController::class);

    Route::apiResource('/sermons', SermonController::class);

    // Sermon publication route
    Route::post('/sermons/{sermon}/toggle-publish', [SermonController::class, 'togglePublish']);

    // Sermon Favorites routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteSermonController::class, 'getFavorites']);
        Route::post('/{sermon}/add', [FavoriteSermonController::class, 'addFavorite']);
        Route::delete('/{sermon}/remove', [FavoriteSermonController::class, 'removeFavorite']);
        Route::post('/{sermon}/toggle', [FavoriteSermonController::class, 'toggleFavorite']);
        Route::get('/{sermon}/check', [FavoriteSermonController::class, 'isFavorite']);
    });
});

//Preachers routes group
Route::middleware('auth:sanctum')->group(function () {
    // Preacher profile routes
    Route::apiResource('/preachers/profiles', PreacherProfileController::class);
    Route::get('/preachers/latest', [PreacherListController::class, 'getLatestPreachers']);
    Route::get('/preachers/{preacherId}/sermons', [PreacherListController::class, 'getSermonsByPreacher']);
    //Dashboard preacher routes group in DashboardPreacherController
    Route::get('/preachers/dashboard', [DashboardPreacherController::class, 'dashboard']);
    Route::get('/preachers/dashboard/{id}', [DashboardPreacherController::class, 'dashboardById']);
});

// Donations routes group (Mobile Money)
Route::prefix('donations')->group(function () {
    // Public route: Shwary callback webhook (no auth required)
    Route::post('/callback', [DonationController::class, 'handleCallback'])->name('donations.callback');

    // Public route: Get supported countries
    Route::get('/countries', [DonationController::class, 'getSupportedCountries']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [DonationController::class, 'index']);
        Route::post('/', [DonationController::class, 'store']);
        Route::get('/statistics', [DonationController::class, 'getStatistics']);
        Route::get('/{uuid}', [DonationController::class, 'show']);
        Route::post('/{uuid}/check-status', [DonationController::class, 'checkStatus']);
    });
});

// Admin Statistics routes (protected + admin role required)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Statistics
    Route::get('/stats', [AdminStatsController::class, 'index']);
    Route::get('/stats/quick', [AdminStatsController::class, 'quickStats']);
    Route::get('/stats/geographical', [AdminStatsController::class, 'geographical']);

    // Management Summary
    Route::get('/management/summary', [AdminManagementController::class, 'summary']);

    // Churches Management
    Route::prefix('churches')->group(function () {
        Route::get('/', [AdminManagementController::class, 'listChurches']);
        Route::get('/{id}', [AdminManagementController::class, 'showChurch']);
        Route::post('/{id}/toggle-status', [AdminManagementController::class, 'toggleChurchStatus']);
        Route::post('/{id}/activate', [AdminManagementController::class, 'activateChurch']);
        Route::post('/{id}/deactivate', [AdminManagementController::class, 'deactivateChurch']);
        Route::post('/bulk-status', [AdminManagementController::class, 'bulkUpdateChurchStatus']);
    });

    // Preachers Management
    Route::prefix('preachers')->group(function () {
        Route::get('/', [AdminManagementController::class, 'listPreachers']);
        Route::get('/{id}', [AdminManagementController::class, 'showPreacher']);
        Route::post('/{id}/toggle-status', [AdminManagementController::class, 'togglePreacherStatus']);
        Route::post('/{id}/activate', [AdminManagementController::class, 'activatePreacher']);
        Route::post('/{id}/deactivate', [AdminManagementController::class, 'deactivatePreacher']);
        Route::post('/bulk-status', [AdminManagementController::class, 'bulkUpdatePreacherStatus']);
    });
});
