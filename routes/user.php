<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserTicketController;
use App\Http\Controllers\User\SettingsController;

Route::middleware('role:user')
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])
            ->name('dashboard');

        // ── USER TICKETS ────────────────────────────────
        Route::middleware(['permission:view_ticket'])->group(function () {
            Route::get('/tickets', [UserTicketController::class, 'index'])
                ->name('tickets.index');
            Route::get('/tickets/{ticket}', [UserTicketController::class, 'show'])
                ->name('tickets.show')
                ->where('ticket', '[0-9]+');
            Route::get('/tickets/{ticket}/attachments/{attachment}', [UserTicketController::class, 'downloadAttachment'])
                ->name('tickets.attachments.download');
        });

        Route::middleware(['permission:create_ticket'])->group(function () {
            Route::get('/tickets/create', [UserTicketController::class, 'create'])
                ->name('tickets.create');
            Route::post('/tickets', [UserTicketController::class, 'store'])
                ->name('tickets.store');
        });

        Route::middleware(['permission:edit_ticket'])->group(function () {
            Route::post('/tickets/{ticket}/messages', [UserTicketController::class, 'storeMessage'])
                ->name('tickets.messages.store');
            Route::post('/tickets/{ticket}/attachments', [UserTicketController::class, 'storeAttachment'])
                ->name('tickets.attachments.store');
        });

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    });