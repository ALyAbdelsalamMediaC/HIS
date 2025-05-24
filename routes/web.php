<?php

use App\Http\Controllers\WEB\AdminAuthController;
use App\Http\Controllers\WEB\CategoryController;
use App\Http\Controllers\WEB\MediaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


// Route::get('/login', function () {
//     return redirect()->route('admin.login');
// })->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', function () { return view('admin.dashboard');})->name('admin.dashboard');
        Route::resource('categories', CategoryController::class);

        Route::get('/media/upload', [MediaController::class, 'show']);
        Route::post('/media/upload', [MediaController::class, 'store']);
});

// Route::prefix('admin')->group(function() {
    // Login
    Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('admin/login', [AdminAuthController::class, 'login']);

    // Registration
    Route::get('register', [AdminAuthController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('register', [AdminAuthController::class, 'register']);

    // Dashboard (protected)
    // Route::middleware('auth')->get('dashboard', fn() => view('admin.dashboard'))->name('dashboard');
// });

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('admin.login');
})->name('logout');