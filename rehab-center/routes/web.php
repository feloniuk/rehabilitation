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

// Admin routes с middleware в маршрутах
Route::middleware(['auth', 'role:admin,master'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Записи - доступно для всех админов и мастеров
    Route::get('appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');
    Route::patch('appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::delete('appointments/{appointment}', [AdminAppointmentController::class, 'destroy'])->name('appointments.destroy');
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('masters', AdminMasterController::class);
        Route::resource('services', AdminServiceController::class);
        Route::resource('pages', PageController::class);
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// Page routes - ВАЖЛИВО: має бути в кінці
Route::get('/{slug}', function ($slug) {
    $page = \App\Models\Page::findBySlug($slug);
    if (!$page) {
        abort(404);
    }
    return view('pages.show', compact('page'));
})->name('pages.show');