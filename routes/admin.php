<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Churches\Index as ChurchesIndex;
use App\Livewire\Admin\Categories\Index as CategoriesIndex;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\PreacherProfiles\Index as PreacherProfilesIndex;
use App\Livewire\Admin\PreacherProfiles\Show as PreacherProfilesShow;
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
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    // Churches
    Route::prefix('churches')->name('churches.')->group(function () {
        Route::get('/', ChurchesIndex::class)->name('index');
        Route::get('/{church}', \App\Livewire\Admin\Churches\Show::class)->name('show');
    });

    // Users
    Route::get('/users', UsersIndex::class)->name('users.index');

    // Preacher Profiles
    Route::prefix('preacher-profiles')->name('preacher-profiles.')->group(function () {
        Route::get('/', PreacherProfilesIndex::class)->name('index');
        Route::get('/{preacherProfile}', PreacherProfilesShow::class)->name('show');
    });

    // Categories
    Route::get('/categories', CategoriesIndex::class)->name('categories.index');

    // Roles
    Route::get('/roles', RolesIndex::class)->name('roles.index');
});