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

// Public Knowledge Base
Route::group(['prefix' => 'kb'], function () {
    Route::get('/', [\App\Http\Controllers\PublicKbController::class, 'index'])->name('public.kb.index');
    Route::get('/category/{slug}', [\App\Http\Controllers\PublicKbController::class, 'category'])->name('public.kb.category');
    Route::get('/article/{slug}', [\App\Http\Controllers\PublicKbController::class, 'show'])->name('public.kb.show');
});

// Authenticated + Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Profile management
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::patch('/profile/notifications', 'updateNotificationSettings')->name('profile.notifications.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
    
    // Notifications
    Route::controller(\App\Http\Controllers\NotificationController::class)->group(function () {
        Route::get('/notifications', 'index')->name('notifications.index');
        Route::patch('/notifications/{id}/mark-as-read', 'markAsRead')->name('notifications.markAsRead');
        Route::patch('/notifications/mark-all-as-read', 'markAllAsRead')->name('notifications.markAllAsRead');
    });

    // Include modular route files
    require __DIR__ . '/user.php';
    require __DIR__ . '/admin.php';
    require __DIR__ . '/manager.php';
    require __DIR__ . '/agent.php';

    // Staff Knowledge Base Routes (Admin & Agent)
    Route::middleware(['role:admin|manager|agent'])->prefix('staff/kb')->name('staff.kb.')->group(function () {
        Route::resource('categories', \App\Http\Controllers\Staff\KbCategoryController::class)->except(['show']);
        Route::resource('articles', \App\Http\Controllers\Staff\KbArticleController::class)->except(['show']);
    });

    // Pusher Test Routes
    Route::get('/pusher-test', fn () => Inertia::render('PusherTest'))
        ->name('pusher.test');
    Route::post('/pusher-send', function (\Illuminate\Http\Request $request) {
        broadcast(new \App\Events\MyEvent($request->input('message')));
        return response()->json(['status' => 'success']);
    })->name('pusher.send');
});

require __DIR__ . '/auth.php';