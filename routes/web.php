<?php
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentManagementController;
use App\Http\Controllers\Admin\userManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\AdminUserController;
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
    Route::middleware('permission:manage_users|manage_roles|manage_permissions')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('admindashboard');

           // ── TICKETS ──────────────────────────────────
            Route::get('/tickets', [AdminTicketController::class, 'index'])
                ->name('tickets.index');

            Route::get('/all', [AdminTicketController::class, 'all'])
                ->name('tickets.all');               

            Route::get('/open', [AdminTicketController::class, 'open'])
                ->name('tickets.open');
                
            Route::get('/assigned', [AdminTicketController::class, 'assigned'])
                ->name('tickets.assigned');

            Route::get('/tickets/create', [AdminTicketController::class, 'create'])
                ->name('tickets.create');
            Route::post('/tickets', [AdminTicketController::class, 'store'])
                ->name('tickets.store');
            Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])
                ->name('tickets.show');
            Route::patch('/tickets/{ticket}', [AdminTicketController::class, 'update'])
                ->name('tickets.update');
            Route::post('/tickets/{ticket}/messages', [AdminTicketController::class, 'storeMessage'])
                ->name('tickets.messages.store');
            Route::post('/tickets/{ticket}/attachments', [AdminTicketController::class, 'storeAttachment'])
                ->name('tickets.attachments.store');
            Route::get('/tickets/{ticket}/attachments/{attachment}', [AdminTicketController::class, 'downloadAttachment'])
                ->name('tickets.attachments.download');

            // ── USER ──────────────────────────────────
            Route::get('/users',[AdminUserController::class, 'index'])
                ->name('users.index');
            Route::get('/users/all',[AdminUserController::class, 'all'])
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
            Route::post('/admin/users/stop-impersonate', [UserManagementController::class, 'stopImpersonate'])
                ->name('admin.users.stop-impersonate');

            // ── ROLES (PART OF USER) ──────────────────────────────────
            Route::get('/roles', [RoleManagementController::class, 'index'])      // index()
                ->name('users.roles');

            Route::post('/roles', [RoleManagementController::class, 'store'])     // store()
                ->name('roles.store');

            Route::put('/roles/{id}', [RoleManagementController::class, 'update']) // update()
                ->name('roles.update');

            Route::delete('/roles/{id}', [RoleManagementController::class, 'destroy']) // destroy()
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


        // ── Manager Area ──────────────────────────────────
        Route::middleware(['auth', 'verified', 'permission:manage_users|manage_roles'])
            ->prefix('manager')
            ->name('manager.')
            ->group(function () {
                Route::get('/dashboard', fn() => Inertia::render('Manager/ManagerDashboard'))
                    ->name('managerdashboard');

                // Example manager routes
                Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
                Route::get('/assigned', [AdminTicketController::class, 'assigned'])->name('tickets.assigned');
            });

        // ── Agent Area ────────────────────────────────────
        Route::middleware(['auth', 'verified', 'permission:view_ticket|edit_ticket'])
            ->prefix('agent')
            ->name('agent.')
            ->group(function () {
                Route::get('/dashboard', fn() => Inertia::render('Agent/AgentDashboard'))
                    ->name('agentdashboard');

                // Example agent routes
                Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
                Route::post('/tickets/{ticket}/messages', [AdminTicketController::class, 'storeMessage'])->name('tickets.messages.store');
            });
});

require __DIR__ . '/auth.php';