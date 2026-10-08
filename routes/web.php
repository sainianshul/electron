<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::prefix('admin')->group(function () {
    // Auth Routes (Named login instead of admin.login)
    Route::get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

    Route::name('admin.')->group(function () {

    Route::middleware(['auth'])->group(function () {
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
            Route::get('{user}/products/data', [\App\Http\Controllers\Admin\UserController::class, 'userProductsData'])->name('products.data');
            Route::get('{user}/requirements/data', [\App\Http\Controllers\Admin\UserController::class, 'userRequirementsData'])->name('requirements.data');
            Route::get('{user}/leads/data', [\App\Http\Controllers\Admin\UserController::class, 'userLeadsData'])->name('leads.data');
            Route::get('{user}/recent-views/data', [\App\Http\Controllers\Admin\UserController::class, 'userRecentViewsData'])->name('recent-views.data');
        });
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        // Comments (Admin Notes)
        Route::post('comments', [\App\Http\Controllers\Admin\CommentController::class, 'store'])->name('comments.store');
        Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('comments.destroy');


    });
    });
});