<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agent\AgentDashboardController;
use App\Http\Controllers\Agent\TicketController;
use App\Http\Controllers\Agent\KnowledgeBaseController;
use App\Http\Controllers\Agent\SettingsController;
use App\Http\Controllers\ProfileController;

Route::middleware(['role:agent'])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {

        // ────────────────────────────────────────────────
        // Dashboard
        // ────────────────────────────────────────────────
        Route::get('/dashboard', [AgentDashboardController::class, 'index'])
            ->name('dashboard');

        // Convenience redirect: /agent → /agent/dashboard
        Route::redirect('/', '/dashboard');


        // ────────────────────────────────────────────────
        // Tickets (matches the "Tickets" menu + sub-items)
        // ────────────────────────────────────────────────
        Route::prefix('tickets')->group(function () {

            // View routes
            Route::middleware(['permission:view_ticket'])->group(function () {
                Route::get('/', [TicketController::class, 'index'])->name('tickets.index');
                Route::get('/open',    [TicketController::class, 'open'])   ->name('tickets.open');
                Route::get('/pending', [TicketController::class, 'pending'])->name('tickets.pending');
                Route::get('/resolved',[TicketController::class, 'resolved'])->name('tickets.resolved');
                Route::get('/closed',  [TicketController::class, 'closed'])  ->name('tickets.closed');
                Route::get('/{ticket}',       [TicketController::class, 'show'])   ->name('tickets.show');
                Route::get('/{ticket}/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])->name('tickets.attachments.download');
            });

            // Create routes
            Route::middleware(['permission:create_ticket'])->group(function () {
                Route::get('/create',  [TicketController::class, 'create']) ->name('tickets.create');
                Route::post('/',       [TicketController::class, 'store'])  ->name('tickets.store');
            });

            // Edit routes
            Route::middleware(['permission:edit_ticket'])->group(function () {
                Route::get('/{ticket}/edit',  [TicketController::class, 'edit'])   ->name('tickets.edit');
                Route::patch('/{ticket}',     [TicketController::class, 'update'])->name('tickets.update');
                Route::post('/{ticket}/reply',   [TicketController::class, 'reply'])   ->name('tickets.reply');
                Route::post('/{ticket}/resolve', [TicketController::class, 'resolve']) ->name('tickets.resolve');
                Route::post('/{ticket}/reopen',  [TicketController::class, 'reopen'])  ->name('tickets.reopen');
                Route::post('/{ticket}/attachments', [TicketController::class, 'storeAttachment'])->name('tickets.attachments.store');
            });
        });


        // ────────────────────────────────────────────────
        // Knowledge Base is now handled in web.php under /staff/kb
        // ────────────────────────────────────────────────


        // ────────────────────────────────────────────────
        // Settings (matches children under "Settings")
        // ────────────────────────────────────────────────
        Route::prefix('settings')->group(function () {
            Route::get('/notifications', [SettingsController::class, 'notifications'])
                ->name('settings.notifications');

            Route::patch('/notifications', [SettingsController::class, 'updateNotifications'])
                ->name('settings.notifications.update');

            Route::get('/signature', [SettingsController::class, 'signature'])
                ->name('settings.signature');

            Route::patch('/signature', [SettingsController::class, 'updateSignature'])
                ->name('settings.signature.update');

            // You can easily add more later, for example:
            // Route::get('/canned-responses', ...) → name('settings.canned-responses')
            // Route::get('/availability', ...)     → name('settings.availability')
        });


        // ────────────────────────────────────────────────
        // Profile (used in Settings → Profile)
        // Usually shared across roles
        // ────────────────────────────────────────────────
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])
                ->name('profile.edit');

            Route::patch('/', [ProfileController::class, 'update'])
                ->name('profile.update');

            // Optional – if you have avatar upload
            // Route::post('/avatar', [ProfileController::class, 'updateAvatar'])
            //     ->name('profile.avatar');
        });


        // ────────────────────────────────────────────────
        // Logout (optional – if you want role-specific logout)
        // Most applications handle this globally
        // ────────────────────────────────────────────────
        // Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        //     ->name('logout');
    });