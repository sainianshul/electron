<?php

use Illuminate\Support\Facades\Route;

// ── Root / Landing Redirection ──────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin() 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
});

// ── Authentication (Accessible to both Admin & Users) ────────────────
Route::get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.post');
Route::post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
Route::get('admin/login', function () {
    return redirect()->route('login');
});

// ── Admin Area (Protected by 'auth' & 'admin' role middleware) ───────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats'])->name('dashboard.stats');

    // Profile
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile.index');
    Route::post('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // Users (Buyers/Sellers) CRUD
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('data', [\App\Http\Controllers\Admin\UserController::class, 'data'])->name('data');
        Route::get('search', [\App\Http\Controllers\Admin\UserController::class, 'search'])->name('search');
        Route::get('blocked', [\App\Http\Controllers\Admin\UserController::class, 'blocked'])->name('blocked');
        Route::get('blocked/data', [\App\Http\Controllers\Admin\UserController::class, 'blockedData'])->name('blocked.data');
        Route::post('{user}/unblock', [\App\Http\Controllers\Admin\UserController::class, 'unblock'])->name('unblock');
        Route::get('deleted', [\App\Http\Controllers\Admin\UserController::class, 'deleted'])->name('deleted');
        Route::get('deleted/data', [\App\Http\Controllers\Admin\UserController::class, 'deletedData'])->name('deleted.data');
        Route::post('{user}/restore', [\App\Http\Controllers\Admin\UserController::class, 'restore'])->name('restore');
        Route::post('{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus'])->name('update-status');
        Route::post('{user}/revoke-token', [\App\Http\Controllers\Admin\UserController::class, 'revokeToken'])->name('revoke-token');
    });
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    // Devices (Admin Overview) CRUD
    Route::prefix('devices')->name('devices.')->group(function () {
        Route::get('data', [\App\Http\Controllers\Admin\DeviceController::class, 'data'])->name('data');
        Route::post('{device}/status', [\App\Http\Controllers\Admin\DeviceController::class, 'updateStatus'])->name('update-status');
    });
    Route::resource('devices', \App\Http\Controllers\Admin\DeviceController::class);

    // Appliances (Admin Catalog) CRUD
    Route::prefix('appliances')->name('appliances.')->group(function () {
        Route::get('data', [\App\Http\Controllers\Admin\ApplianceController::class, 'data'])->name('data');
        Route::post('{appliance}/status', [\App\Http\Controllers\Admin\ApplianceController::class, 'updateStatus'])->name('update-status');
    });
    Route::resource('appliances', \App\Http\Controllers\Admin\ApplianceController::class);

    // Comments (Admin Notes)
    Route::post('comments', [\App\Http\Controllers\Admin\CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('comments.destroy');

});

// ── User Area (Regular Smart Home Users) ────────────────────────────
Route::middleware(['auth'])->group(function () {

    // User Dashboard
    Route::get('dashboard', [\App\Http\Controllers\User\DashboardController::class, 'index'])->name('user.dashboard');

    // User Appliances Management (Box UI)
    Route::prefix('appliances')->name('user.appliances.')->group(function () {
        Route::get('/', [\App\Http\Controllers\User\ApplianceController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\User\ApplianceController::class, 'store'])->name('store');
        Route::get('/check-availability', [\App\Http\Controllers\User\ApplianceController::class, 'checkAvailability'])->name('check-availability');
        Route::post('/{pin}/toggle', [\App\Http\Controllers\User\ApplianceController::class, 'toggle'])->name('toggle');
        Route::delete('/{pin}', [\App\Http\Controllers\User\ApplianceController::class, 'detach'])->name('detach');
    });

    // User Devices Management
    Route::prefix('devices')->name('user.devices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\User\DeviceController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\DeviceController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\DeviceController::class, 'store'])->name('store');
        Route::get('/{device}', [\App\Http\Controllers\User\DeviceController::class, 'show'])->name('show');
        Route::get('/{device}/edit', [\App\Http\Controllers\User\DeviceController::class, 'edit'])->name('edit');
        Route::put('/{device}', [\App\Http\Controllers\User\DeviceController::class, 'update'])->name('update');
        Route::delete('/{device}', [\App\Http\Controllers\User\DeviceController::class, 'destroy'])->name('destroy');

        // Pin Control & Appliance Assignment
        Route::post('/{device}/pins/{pin}/toggle', [\App\Http\Controllers\User\DeviceController::class, 'togglePin'])->name('pins.toggle');
        Route::post('/{device}/appliances', [\App\Http\Controllers\User\DeviceController::class, 'attachAppliance'])->name('appliances.attach');
        Route::delete('/{device}/pins/{pin}/detach', [\App\Http\Controllers\User\DeviceController::class, 'detachAppliance'])->name('pins.detach');
        Route::post('/{device}/pins/{pin}/assign', [\App\Http\Controllers\User\DeviceController::class, 'assignPin'])->name('pins.assign');
    });

});