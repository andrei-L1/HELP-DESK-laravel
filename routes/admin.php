<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\TicketSettingsController;

// Stop impersonation (applies to all admins)
Route::post('/admin/users/stop-impersonate', [UserManagementController::class, 'stopImpersonate'])
    ->name('admin.users.stop-impersonate');

Route::middleware('role:admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // ── TICKETS ──────────────────────────────────
        // Fix #1 (Critical): Admin ticket routes now guarded by permission middleware,
        // consistent with Manager and User routes.

        // Create new ticket
        Route::middleware(['permission:create_ticket'])->group(function () {
            Route::get('/tickets/create', [AdminTicketController::class, 'create'])
                ->name('tickets.create');
            Route::post('/tickets', [AdminTicketController::class, 'store'])
                ->name('tickets.store');
        });

        // View & navigate
        Route::middleware(['permission:view_ticket'])->group(function () {
            Route::get('/tickets', [AdminTicketController::class, 'index'])
                ->name('tickets.index');
            Route::get('/all', [AdminTicketController::class, 'all'])
                ->name('tickets.all');
            Route::get('/assigned', [AdminTicketController::class, 'assigned'])
                ->name('tickets.assigned');
            Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])
                ->name('tickets.show');
            Route::get('/tickets/{ticket}/attachments/{attachment}', [AdminTicketController::class, 'downloadAttachment'])
                ->name('tickets.attachments.download');

            // Bulk operations
            Route::delete('/tickets-bulk/destroy', [AdminTicketController::class, 'bulkDestroy'])
                ->name('tickets.bulk-destroy');
            Route::post('/tickets-bulk/update', [AdminTicketController::class, 'bulkUpdate'])
                ->name('tickets.bulk-update');
        });

        // Mutate existing ticket
        Route::middleware(['permission:edit_ticket'])->group(function () {
            Route::patch('/tickets/{ticket}', [AdminTicketController::class, 'update'])
                ->name('tickets.update');
            Route::post('/tickets/{ticket}/messages', [AdminTicketController::class, 'storeMessage'])
                ->name('tickets.messages.store');
            Route::post('/tickets/{ticket}/attachments', [AdminTicketController::class, 'storeAttachment'])
                ->name('tickets.attachments.store');
        });

        // ── USER ──────────────────────────────────
        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');
        Route::get('/users/all', [AdminUserController::class, 'all'])
            ->name('users.all');
        Route::get('/users/create', [UserManagementController::class, 'create'])
            ->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])
            ->name('users.store');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])
            ->name('users.show');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])
            ->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])
            ->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])
            ->name('users.destroy');

        // Bulk operations
        Route::delete('/users-bulk/destroy', [UserManagementController::class, 'bulkDestroy'])
            ->name('users.bulk-destroy');
        Route::post('/users-bulk/update', [UserManagementController::class, 'bulkUpdate'])
            ->name('users.bulk-update');

        // Additional user management routes
        Route::put('/users/{user}/password', [UserManagementController::class, 'updatePassword'])
            ->name('users.password.update');
        Route::post('/users/{user}/avatar', [UserManagementController::class, 'updateAvatar'])
            ->name('users.avatar.update');
        Route::post('/users/{user}/impersonate', [UserManagementController::class, 'impersonate'])
            ->name('users.impersonate');
        Route::delete('/users/{user}/sessions/{session}', [UserManagementController::class, 'logoutSession'])
            ->name('users.sessions.destroy');
        Route::delete('/users/{user}/sessions', [UserManagementController::class, 'logoutAllSessions'])
            ->name('users.sessions.destroy-all');

        // ── ROLES (PART OF USER) ──────────────────────────────────
        Route::get('/roles', [RoleManagementController::class, 'index'])
            ->name('users.roles');

        Route::post('/roles', [RoleManagementController::class, 'store'])
            ->name('roles.store');

        Route::put('/roles/{id}', [RoleManagementController::class, 'update'])
            ->name('roles.update');

        Route::delete('/roles/{id}', [RoleManagementController::class, 'destroy'])
            ->name('roles.destroy');

        // ── DEPARTMENT ──────────────────────────────────
        Route::get('/departments', [DepartmentManagementController::class, 'index'])
            ->name('departments.index');
        Route::post('/departments', [DepartmentManagementController::class, 'store'])
            ->name('departments.store');
        Route::get('/departments/{id}', [DepartmentManagementController::class, 'show'])
            ->name('departments.show');
        Route::put('/departments/{id}', [DepartmentManagementController::class, 'update'])
            ->name('departments.update');
        Route::delete('/departments/{id}', [DepartmentManagementController::class, 'destroy'])
            ->name('departments.destroy');

        // Department user assignments
        Route::post('/departments/{id}/assign-users', [DepartmentManagementController::class, 'assignUsers'])
            ->name('departments.assign-users');
        Route::delete('/departments/{id}/users/{userId}', [DepartmentManagementController::class, 'removeUser'])
            ->name('departments.remove-user');
        Route::post('/departments/{id}/users/{userId}/primary', [DepartmentManagementController::class, 'setPrimary'])
            ->name('departments.set-primary');

        // ── SETTINGS ──────────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('/general', [SettingsController::class, 'general'])->name('general');
            Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');
            Route::get('/ticket', [SettingsController::class, 'ticket'])->name('ticket');

            Route::post('/ticket/priorities', [TicketSettingsController::class, 'storePriority'])->name('priorities.store');
            Route::put('/ticket/priorities/{id}', [TicketSettingsController::class, 'updatePriority'])->name('priorities.update');
            Route::delete('/ticket/priorities/{id}', [TicketSettingsController::class, 'destroyPriority'])->name('priorities.destroy');

            Route::post('/ticket/statuses', [TicketSettingsController::class, 'storeStatus'])->name('statuses.store');
            Route::put('/ticket/statuses/{id}', [TicketSettingsController::class, 'updateStatus'])->name('statuses.update');
            Route::delete('/ticket/statuses/{id}', [TicketSettingsController::class, 'destroyStatus'])->name('statuses.destroy');

            Route::post('/ticket/categories', [TicketSettingsController::class, 'storeCategory'])->name('categories.store');
            Route::put('/ticket/categories/{id}', [TicketSettingsController::class, 'updateCategory'])->name('categories.update');
            Route::delete('/ticket/categories/{id}', [TicketSettingsController::class, 'destroyCategory'])->name('categories.destroy');

            Route::post('/ticket/types', [TicketSettingsController::class, 'storeType'])->name('types.store');
            Route::put('/ticket/types/{id}', [TicketSettingsController::class, 'updateType'])->name('types.update');
            Route::delete('/ticket/types/{id}', [TicketSettingsController::class, 'destroyType'])->name('types.destroy');

            Route::get('/email', [SettingsController::class, 'email'])->name('email');
            Route::post('/email', [SettingsController::class, 'updateEmail'])->name('email.update');
            Route::post('/email/test', [SettingsController::class, 'testEmail'])->name('email.test');
            Route::post('/email/notifications', [SettingsController::class, 'updateNotifications'])->name('email.notifications.update');

            // SLA Policies
            Route::get('/sla', [SettingsController::class, 'sla'])->name('sla');
            Route::post('/sla', [SettingsController::class, 'storeSla'])->name('sla.store');
            Route::put('/sla/{id}', [SettingsController::class, 'updateSla'])->name('sla.update');
            Route::delete('/sla/{id}', [SettingsController::class, 'destroySla'])->name('sla.destroy');
        });
    });