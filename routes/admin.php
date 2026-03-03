<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChurchController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\NotificationController;

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/churches', [ChurchController::class, 'index'])->name('churches.index');
    Route::get('/churches/{church}', [ChurchController::class, 'show'])->name('churches.show');
    Route::patch('/churches/{church}/toggle-active', [ChurchController::class, 'toggleActive'])->name('churches.toggle-active');
    Route::patch('/churches/{church}/toggle-featured', [ChurchController::class, 'toggleFeatured'])->name('churches.toggle-featured');
    Route::patch('/preachers/{preacher}/toggle-active', [ChurchController::class, 'togglePreacherActive'])->name('preachers.toggle-active');
    Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});
