<?php

use App\Http\Controllers\WEB\AdminAuthController;
use App\Http\Controllers\WEB\CategoryController;
use App\Http\Controllers\WEB\MediaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;




Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', function () { return view('pages.admin.dashboard');})->name('pages.admin.dashboard');
        Route::resource('categories', CategoryController::class);

        Route::get('/content/upload', [MediaController::class, 'create']);
        Route::post('/content/upload', [MediaController::class, 'store']);
        Route::get('/content/getall', [MediaController::class, 'getall']);
        Route::get('/content/getone/{id}', [MediaController::class, 'getone']);
    });


    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('admin/login', [AdminAuthController::class, 'login']);

    Route::get('register', [AdminAuthController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('register', [AdminAuthController::class, 'register']);

  

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('admin.login');
})->name('logout');