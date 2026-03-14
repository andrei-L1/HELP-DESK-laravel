<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\NoPasswordController;
use App\Http\Controllers\Auth\GoogleSocialiteController;

// Home / public page
Route::get('/', fn () => Inertia::render('Index', [
    'canLogin'       => Route::has('login'),
    'canRegister'    => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion'     => PHP_VERSION,
]));

// Google OAuth Routes
Route::get('/auth/google', [GoogleSocialiteController::class, 'redirectToGoogle'])
    ->name('google.redirect');
Route::get('/auth/google/callback', [GoogleSocialiteController::class, 'handleGoogleCallback'])
    ->name('google.callback');

// For users who already have a password
Route::put('/password/update', [NoPasswordController::class, 'update'])
    ->name('password.update');

// For OAuth users setting a password for the first time
Route::post('/password/set', [NoPasswordController::class, 'set'])
    ->name('password.set');

// Authenticated + Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Profile management
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // Include modular route files
    require __DIR__ . '/user.php';
    require __DIR__ . '/admin.php';
    require __DIR__ . '/manager.php';
    require __DIR__ . '/agent.php';
});

require __DIR__ . '/auth.php';