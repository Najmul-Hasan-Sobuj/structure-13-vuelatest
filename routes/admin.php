<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\Settings\ProfileController;
use App\Http\Controllers\Admin\Settings\SecurityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\AuthenticateAdmin;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Auth routes (unauthenticated)
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:admin-login')
        ->name('login.store');

    // Admin protected routes
    Route::middleware(AuthenticateAdmin::class.':admin')->group(function () {
        // Dashboard
        Route::get('/', fn () => Inertia::render('admin/Dashboard'))->name('dashboard');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        // Settings - Profile
        Route::redirect('settings', '/admin/settings/profile');
        Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Settings - Security
        Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');
        Route::put('settings/password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('password.update');

        // Settings - Appearance
        Route::inertia('settings/appearance', 'admin/settings/Appearance')->name('appearance.edit');

        // Authorize - Roles & Users
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
    });
});
