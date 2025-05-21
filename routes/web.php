<?php

use App\Http\Controllers\WEB\AdminAuthController;
use App\Http\Controllers\WEB\CategoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
        Route::resource('categories', CategoryController::class);

});

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('admin.login');
})->name('logout');