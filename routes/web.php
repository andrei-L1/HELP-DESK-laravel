<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Index', [
    'canLogin'       => Route::has('login'),
    'canRegister'    => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion'     => PHP_VERSION,
]));

// ────────────────────────────────────────────────
// Authenticated + Verified Routes
// ────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // ── User Area ───────────────────────────────────
    Route::get('/dashboard', fn () => Inertia::render('Users/Dashboard'))
        ->name('dashboard');

    // Profile management
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // ── Admin Area ──────────────────────────────────
    Route::middleware('admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('admindashboard');

            // Tickets
            Route::get('/tickets', [AdminTicketController::class, 'index'])
                ->name('tickets.index');

            Route::get('/open', [AdminTicketController::class, 'open'])
                ->name('tickets.open');
                
            Route::get('/assigned', [AdminTicketController::class, 'assigned'])
                ->name('tickets.assigned');


            // Add more admin routes here in the future, e.g.:
            // Route::get('/users',    [AdminUserController::class, 'index'])->name('users.index');
            // Route::get('/settings', fn () => Inertia::render('Admin/Settings'))->name('settings');
        });
});

require __DIR__ . '/auth.php';