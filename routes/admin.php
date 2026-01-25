<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Churches\Index as ChurchesIndex;
use App\Livewire\Admin\Categories\Index as CategoriesIndex;
use App\Livewire\Admin\Churches\Show;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\PreacherProfiles\Index as PreacherProfilesIndex;
use App\Livewire\Admin\PreacherProfiles\Show as PreacherProfilesShow;
use App\Livewire\Admin\Monitoring\RealtimeDashboard;
use App\Livewire\Admin\Analytics\UserAnalytics;
use App\Livewire\Admin\Storage\DiskUsageMonitor;
use App\Livewire\Admin\Logs\ApiRequestLog;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Logout;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes require authentication and admin role.
|
*/

// Public auth routes
Route::get('/login', Login::class)->name('login');
Route::get('/logout', Logout::class)->name('logout');

// Protected admin routes
Route::middleware(['auth','admin'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard')->lazy();
    // Churches
    Route::prefix('churches')->name('churches.')->group(function () {
        Route::get('/', ChurchesIndex::class)->name('index')->lazy();
        Route::get('/{church}', Show::class)->name('show')->lazy();
    });

    // Users
    Route::get('/users', UsersIndex::class)->name('users.index')->lazy();

    // Preacher Profiles
    Route::prefix('preacher-profiles')->name('preacher-profiles.')->group(function () {
        Route::get('/', PreacherProfilesIndex::class)->name('index')->lazy();
        Route::get('/{preacherProfile}', PreacherProfilesShow::class)->name('show')->lazy();
    });

    // Categories
    Route::get('/categories', CategoriesIndex::class)->name('categories.index')->lazy();
    // Roles
    Route::get('/roles', RolesIndex::class)->name('roles.index')->lazy();

    // Monitoring & Analytics
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/realtime', RealtimeDashboard::class)->name('realtime')->lazy();
    });

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/users', UserAnalytics::class)->name('users')->lazy();
    });

    Route::prefix('storage')->name('storage.')->group(function () {
        Route::get('/monitor', DiskUsageMonitor::class)->name('monitor')->lazy();
    });

    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/api', ApiRequestLog::class)->name('api')->lazy();
    });
});
