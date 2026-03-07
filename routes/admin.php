<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChurchController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\CategorySermonController;
use App\Http\Controllers\Admin\AdminSermonController;
use App\Http\Controllers\Admin\ChurchProfileController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StorageUpgradeController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes require authentication and admin role.
| Prefixed with /admin and named admin.*
|
*/

// Guest routes (login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard (accessible to all admin roles — controller renders role-specific page)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart');

    // Sermons management (church admin + super admin)
    Route::get('/sermons', [AdminSermonController::class, 'index'])->name('sermons.index');
    Route::get('/sermons/create', [AdminSermonController::class, 'create'])->name('sermons.create');
    Route::post('/sermons', [AdminSermonController::class, 'store'])->name('sermons.store');
    Route::get('/sermons/{sermon}/edit', [AdminSermonController::class, 'edit'])->name('sermons.edit');
    Route::put('/sermons/{sermon}', [AdminSermonController::class, 'update'])->name('sermons.update');
    Route::patch('/sermons/{sermon}/toggle-publish', [AdminSermonController::class, 'togglePublish'])->name('sermons.toggle-publish');
    Route::delete('/sermons/{sermon}', [AdminSermonController::class, 'destroy'])->name('sermons.destroy');

    // Church profile (church admin manages their own church)
    Route::get('/church-profile', [ChurchProfileController::class, 'show'])->name('church-profile');
    Route::post('/church-profile', [ChurchProfileController::class, 'update'])->name('church-profile.update');

    // Donations — create/store accessible to all admins
    Route::get('/donations/create', [DonationController::class, 'create'])->name('donations.create');
    Route::post('/donations', [DonationController::class, 'store'])->name('donations.store');
    Route::get('/donations/{uuid}/status', [DonationController::class, 'checkStatus'])->name('donations.check-status');

    // Storage upgrade (church admin)
    Route::get('/storage-upgrade', [StorageUpgradeController::class, 'index'])->name('storage-upgrade.index');
    Route::post('/storage-upgrade', [StorageUpgradeController::class, 'store'])->name('storage-upgrade.store');
    Route::get('/storage-upgrade/{uuid}/status', [StorageUpgradeController::class, 'checkStatus'])->name('storage-upgrade.check-status');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    // Global search
    Route::get('/search', GlobalSearchController::class)->name('search');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Super admin only routes
    Route::middleware('super_admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/churches', [ChurchController::class, 'index'])->name('churches.index');
        Route::get('/churches/{church}', [ChurchController::class, 'show'])->name('churches.show');
        Route::patch('/churches/{church}/toggle-active', [ChurchController::class, 'toggleActive'])->name('churches.toggle-active');
        Route::patch('/churches/{church}/toggle-featured', [ChurchController::class, 'toggleFeatured'])->name('churches.toggle-featured');
        Route::patch('/preachers/{preacher}/toggle-active', [ChurchController::class, 'togglePreacherActive'])->name('preachers.toggle-active');
        Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
        Route::get('/sermon-categories', [CategorySermonController::class, 'index'])->name('sermon-categories.index');
        Route::post('/sermon-categories', [CategorySermonController::class, 'store'])->name('sermon-categories.store');
        Route::patch('/sermon-categories/{categorySermon}', [CategorySermonController::class, 'update'])->name('sermon-categories.update');
        Route::delete('/sermon-categories/{categorySermon}', [CategorySermonController::class, 'destroy'])->name('sermon-categories.destroy');
    });
});
