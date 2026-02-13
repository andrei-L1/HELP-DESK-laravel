<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// ── Protected routes (auth + verified) ────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Normal user dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile routes (already auth-only, but you can keep them here or separate)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Admin area (extra 'admin' middleware + prefix + name prefix) ──
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('/admindashboard', [AdminDashboardController::class, 'index'])
            ->name('admindashboard');

        // Future admin routes examples:
        // Route::get('/users', ...)->name('users.index');
        // Route::get('/settings', fn() => Inertia::render('Admin/Settings'))->name('settings');
    });
});

require __DIR__.'/auth.php';