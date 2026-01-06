<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManualAppointmentController;
use App\Http\Controllers\Admin\MasterController as AdminMasterController;
use App\Http\Controllers\Admin\MasterNotificationLogController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\Platform\AuthController;
use App\Http\Controllers\Platform\PlatformController;
use App\Http\Controllers\Platform\TenantRegistrationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Platform Routes (без tenant context)
|--------------------------------------------------------------------------
*/

Route::name('platform.')->group(function () {
    Route::get('/', [PlatformController::class, 'index'])->name('home');
    Route::get('/pricing', [PlatformController::class, 'pricing'])->name('pricing');
    Route::get('/features', [PlatformController::class, 'features'])->name('features');

    // Registration
    Route::get('/register', [TenantRegistrationController::class, 'create'])->name('register');
    Route::post('/register', [TenantRegistrationController::class, 'store'])->name('register.store');

    // Authentication
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Tenant selection (for users with multiple tenants)
    Route::middleware('auth')->group(function () {
        Route::get('/select-tenant', [AuthController::class, 'selectTenant'])->name('select-tenant');
    });
});

// Alias for login route (Laravel expects 'login' route name)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    // Tenants management
    Route::resource('tenants', SuperAdminTenantController::class);
    Route::post('tenants/{tenant}/toggle-status', [SuperAdminTenantController::class, 'toggleStatus'])
        ->name('tenants.toggle-status');
    Route::post('tenants/{tenant}/impersonate', [SuperAdminTenantController::class, 'impersonate'])
        ->name('tenants.impersonate');
});

/*
|--------------------------------------------------------------------------
| Tenant Routes (з tenant context)
|--------------------------------------------------------------------------
*/

Route::prefix('{tenant}')->middleware(['tenant'])->name('tenant.')->group(function () {
    // Public tenant pages
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/masters/{master}', [MasterController::class, 'show'])->name('masters.show');

    // Appointment booking
    Route::get('/appointment/create', [AppointmentController::class, 'create'])->name('appointment.create');
    Route::post('/appointment', [AppointmentController::class, 'store'])->name('appointment.store');
    Route::get('/appointment/success', [AppointmentController::class, 'success'])->name('appointment.success');
    Route::patch('/appointment/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointment.cancel');

    // AJAX routes
    Route::get('/masters/{master}/available-slots/{date}/{service}', [MasterController::class, 'getAvailableSlots'])
        ->name('masters.available-slots');

    // Dynamic pages
    Route::get('/page/{slug}', function ($tenant, $slug) {
        $page = \App\Models\Page::findBySlug($slug);
        if (!$page) {
            abort(404);
        }
        return view('pages.show', compact('page'));
    })->name('pages.show');

    /*
    |--------------------------------------------------------------------------
    | Tenant Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth', 'tenant.role:owner,admin,master'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('select-date', [DashboardController::class, 'selectDate'])->name('select-date');
        Route::post('load-calendar', [DashboardController::class, 'loadCalendar'])->name('load-calendar');

        // Appointments - accessible for all admins and masters
        Route::get('appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');

        Route::get('appointments/create-manual', [ManualAppointmentController::class, 'create'])
            ->name('appointments.manual.create');
        Route::post('appointments/create-manual', [ManualAppointmentController::class, 'store'])
            ->name('appointments.manual.store');

        // API for client search (Select2)
        Route::get('appointments/search-clients', [ManualAppointmentController::class, 'searchClients'])
            ->name('appointments.search-clients');
        Route::get('appointments/master-services', [ManualAppointmentController::class, 'getMasterServices'])
            ->name('appointments.get-master-services');
        Route::get('appointments/get-service-price', [ManualAppointmentController::class, 'getServicePrice'])
            ->name('appointments.get-service-price');

        Route::get('appointments/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');
        Route::get('appointments/{appointment}/edit', [AdminAppointmentController::class, 'edit'])->name('appointments.edit');
        Route::put('appointments/{appointment}', [AdminAppointmentController::class, 'update'])->name('appointments.update');
        Route::patch('appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
        Route::patch('appointments/{appointment}/toggle-confirm', [AdminAppointmentController::class, 'toggleConfirm'])->name('appointments.toggle-confirm');
        Route::delete('appointments/{appointment}', [AdminAppointmentController::class, 'destroy'])->name('appointments.destroy');

        // Owner and Admin only routes
        Route::middleware('tenant.role:owner,admin')->group(function () {
            Route::resource('clients', ClientController::class);
            Route::resource('masters', AdminMasterController::class);
            Route::resource('services', AdminServiceController::class);

            // Pages management
            Route::get('pages', [PageController::class, 'index'])->name('pages.index');
            Route::get('pages/home/edit', [PageController::class, 'editHome'])->name('pages.edit-home');
            Route::get('pages/home/blocks/create', [PageController::class, 'createBlock'])->name('pages.blocks.create');
            Route::post('pages/home/blocks', [PageController::class, 'storeBlock'])->name('pages.blocks.store');
            Route::put('pages/home/blocks/{id}', [PageController::class, 'updateBlock'])->name('pages.blocks.update');
            Route::delete('pages/home/blocks/{id}', [PageController::class, 'destroyBlock'])->name('pages.blocks.destroy');

            Route::get('pages/create', [PageController::class, 'create'])->name('pages.create');
            Route::post('pages', [PageController::class, 'store'])->name('pages.store');
            Route::get('pages/{id}/edit', [PageController::class, 'edit'])->name('pages.edit');
            Route::put('pages/{id}', [PageController::class, 'update'])->name('pages.update');
            Route::delete('pages/{id}', [PageController::class, 'destroy'])->name('pages.destroy');

            // Settings
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

            // Notifications
            Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
            Route::get('notifications/logs', [NotificationController::class, 'logs'])->name('notifications.logs');
            Route::post('notifications/preview', [NotificationController::class, 'previewTemplate'])->name('notifications.preview');
            Route::post('appointments/{appointment}/quick-reminder', [NotificationController::class, 'quickReminder'])
                ->name('appointments.quick-reminder');

            // Notification templates
            Route::get('notifications/templates', [NotificationController::class, 'templates'])->name('notifications.templates');
            Route::post('notifications/templates', [NotificationController::class, 'storeTemplate'])->name('notifications.templates.store');
            Route::get('notifications/templates/{id}/edit', function ($tenant, $id) {
                $template = \App\Models\NotificationTemplate::findOrFail($id);
                return response()->json($template);
            })->name('notifications.templates.edit');
            Route::put('notifications/templates/{id}', [NotificationController::class, 'updateTemplate'])->name('notifications.templates.update');
            Route::delete('notifications/templates/{id}', [NotificationController::class, 'deleteTemplate'])->name('notifications.templates.delete');

            // Master notification logs
            Route::get('master-notification-logs', [MasterNotificationLogController::class, 'index'])->name('master-notification-logs.index');
            Route::get('master-notification-logs/{masterNotificationLog}', [MasterNotificationLogController::class, 'show'])->name('master-notification-logs.show');
        });

        // Owner only routes
        Route::middleware('tenant.role:owner')->group(function () {
            // Billing (placeholder for now)
            Route::get('billing', function ($tenant) {
                return view('tenant.admin.billing.index', ['tenant' => app('currentTenant')]);
            })->name('billing.index');

            // Team management (placeholder for now)
            Route::get('team', function ($tenant) {
                return view('tenant.admin.team.index', ['tenant' => app('currentTenant')]);
            })->name('team.index');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Legacy Routes (для backward compatibility під час міграції)
|--------------------------------------------------------------------------
*/

// DEBUG route
Route::get('/debug/config', function () {
    if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
        abort(403, 'Unauthorized');
    }

    return response()->json([
        'session_driver' => config('session.driver'),
        'session_lifetime' => config('session.lifetime'),
        'app_debug' => config('app.debug'),
        'app_env' => config('app.env'),
        'csrf_token_length' => strlen(csrf_token()),
        'session_id' => session()->getId(),
        'timestamp' => now()->toIso8601String(),
    ], 200, [], JSON_PRETTY_PRINT);
})->name('debug.config');
