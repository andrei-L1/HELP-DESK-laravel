<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminTicketController;
use Inertia\Inertia;

Route::middleware(['role:agent'])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {
        Route::get('/dashboard', fn() => Inertia::render('Agent/Dashboard'))
            ->name('dashboard');

        // Example agent routes
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::post('/tickets/{ticket}/messages', [AdminTicketController::class, 'storeMessage'])->name('tickets.messages.store');
    });