<?php

use App\Http\Controllers\Settings\MailSettingsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\QueueMonitorController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

    Route::get('settings/queue', [QueueMonitorController::class, 'edit'])->name('queue-monitor.edit');

    Route::get('settings/mail', [MailSettingsController::class, 'edit'])->name('mail-settings.edit');
    Route::patch('settings/mail', [MailSettingsController::class, 'update'])->name('mail-settings.update');
    Route::post('settings/mail/test', [MailSettingsController::class, 'sendTest'])
        ->middleware('throttle:3,1')
        ->name('mail-settings.test');
});
