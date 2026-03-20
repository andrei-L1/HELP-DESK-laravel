<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\Manager\ManagerTeamController;
use App\Http\Controllers\Manager\ManagerTicketController;
use App\Http\Controllers\Manager\ManagerReportController;
use App\Http\Controllers\Manager\SettingsController;

Route::middleware(['role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])
            ->name('dashboard');

        // manager ticket viewing operations
        Route::middleware(['permission:view_ticket'])->group(function () {
            Route::get('/tickets', [ManagerTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/all', [ManagerTicketController::class, 'all'])->name('tickets.all');
            Route::get('/tickets/open', [ManagerTicketController::class, 'open'])->name('tickets.open');
            Route::get('/tickets/assigned', [ManagerTicketController::class, 'assigned'])->name('tickets.assigned');
            Route::get('/tickets/{ticket}', [ManagerTicketController::class, 'show'])->name('tickets.show')->where('ticket', '[0-9]+');
            Route::get('/tickets/{ticket}/attachments/{attachment}', [ManagerTicketController::class, 'downloadAttachment'])->name('tickets.attachments.download');
        });

        // manager ticket creation operations
        Route::middleware(['permission:create_ticket'])->group(function () {
            Route::get('/tickets/create', [ManagerTicketController::class, 'create'])->name('tickets.create');
            Route::post('/tickets', [ManagerTicketController::class, 'store'])->name('tickets.store');
        });

        // manager ticket editing operations
        Route::middleware(['permission:edit_ticket'])->group(function () {
            Route::patch('/tickets/{ticket}', [ManagerTicketController::class, 'update'])->name('tickets.update');
            Route::post('/tickets/{ticket}/messages', [ManagerTicketController::class, 'storeMessage'])->name('tickets.messages.store');
            Route::post('/tickets/{ticket}/attachments', [ManagerTicketController::class, 'storeAttachment'])->name('tickets.attachments.store');
        });

        // manager team operations
        Route::middleware(['permission:manage_users'])->group(function () {
            Route::get('/team', [ManagerTeamController::class, 'index'])->name('team.index');
            Route::get('/team/{user}', [ManagerTeamController::class, 'show'])->name('team.show')->where('user', '[0-9]+');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ManagerReportController::class, 'index'])->name('index');
            Route::get('/export', [ManagerReportController::class, 'export'])->name('export');
        });

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    });