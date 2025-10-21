<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterController as AdminMasterController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\ManualAppointmentController;
use App\Http\Controllers\Admin\NotificationController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/masters/{master}', [MasterController::class, 'show'])->name('masters.show');

// Appointment routes
Route::get('/appointment/create', [AppointmentController::class, 'create'])->name('appointment.create');
Route::post('/appointment', [AppointmentController::class, 'store'])->name('appointment.store');
Route::get('/appointment/success', [AppointmentController::class, 'success'])->name('appointment.success');
Route::patch('/appointment/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointment.cancel');

// AJAX routes
Route::get('/masters/{master}/available-slots/{date}/{service}', [MasterController::class, 'getAvailableSlots'])
     ->name('masters.available-slots');

// Auth routes
Auth::routes(['register' => false]);

// Admin routes
Route::middleware(['auth', 'role:admin,master'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Записи - доступно для всіх админів і майстрів
    Route::get('appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
    
    Route::get('appointments/create-manual', [ManualAppointmentController::class, 'create'])
        ->name('appointments.manual.create');
    Route::post('appointments/create-manual', [ManualAppointmentController::class, 'store'])
        ->name('appointments.manual.store');
    
    // API для пошуку клієнтів (для Select2)
    Route::get('appointments/search-clients', [ManualAppointmentController::class, 'searchClients'])
        ->name('appointments.search-clients');
    
    Route::get('appointments/get-service-price', [ManualAppointmentController::class, 'getServicePrice'])
        ->name('appointments.get-service-price');
        
    Route::get('appointments/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');
    Route::patch('appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::delete('appointments/{appointment}', [AdminAppointmentController::class, 'destroy'])->name('appointments.destroy');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('masters', AdminMasterController::class);
        Route::resource('services', AdminServiceController::class);

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
        
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::post('notifications/send', [NotificationController::class, 'send'])
            ->name('notifications.send');
        Route::get('notifications/logs', [NotificationController::class, 'logs'])
            ->name('notifications.logs');
        Route::post('notifications/preview', [NotificationController::class, 'previewTemplate'])
            ->name('notifications.preview');
        
        // Управління шаблонами
        Route::get('notifications/templates', [NotificationController::class, 'templates'])
            ->name('notifications.templates');
        Route::post('notifications/templates', [NotificationController::class, 'storeTemplate'])
            ->name('notifications.templates.store');
        Route::get('notifications/templates/{id}/edit', function($id) {
            $template = \App\Models\NotificationTemplate::findOrFail($id);
            return response()->json($template);
        })->name('notifications.templates.edit');
        Route::put('notifications/templates/{id}', [NotificationController::class, 'updateTemplate'])
            ->name('notifications.templates.update');
        Route::delete('notifications/templates/{id}', [NotificationController::class, 'deleteTemplate'])
            ->name('notifications.templates.delete');
        
    });
});

// Page routes - в кінці
Route::get('/{slug}', function ($slug) {
    $page = \App\Models\Page::findBySlug($slug);
    if (!$page) {
        abort(404);
    }
    return view('pages.show', compact('page'));
})->name('pages.show');