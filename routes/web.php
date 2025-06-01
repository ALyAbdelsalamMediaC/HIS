<?php

use App\Http\Controllers\WEB\AdminAuthController;
use App\Http\Controllers\WEB\ArticleController;
use App\Http\Controllers\WEB\CategoryController;
use App\Http\Controllers\WEB\MediaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Web\UserController;




Route::middleware('auth')->group(function () {
    
    Route::get('/', function () {
        return view('pages.admin.dashboard');
    })->name('pages.admin.dashboard');

    Route::resource('categories', CategoryController::class);

    Route::get('/content', [MediaController::class, 'validation'])->name('content.validation');
    Route::get('/content/upload', [MediaController::class, 'create']);
    Route::post('/content/upload', [MediaController::class, 'store']);
    Route::get('/content/getall', [MediaController::class, 'getall']);
    Route::get('/content/getone/{id}', [MediaController::class, 'getone']);
    Route::get('/content/recently_Added', [MediaController::class, 'recently_Added']);

    Route::get('/article/upload', [ArticleController::class, 'create']);
    Route::post('/article/upload', [ArticleController::class, 'store']);
    Route::get('/article/getall', [ArticleController::class, 'getall'])->name('article.getall');
    Route::get('/article/getone/{id}', [ArticleController::class, 'getone']);
    Route::get('/article/recently_Added', [ArticleController::class, 'recently_Added']);

    Route::resource('users', UserController::class, [
        'names' => [
            'index' => 'users.index',
            'edit' => 'users.edit',
            'update' => 'users.update',
            'destroy' => 'users.destroy',
        ]
    ])->except(['create', 'store', 'show']);
    Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::get('users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::post('users/change-password', [UserController::class, 'changePassword'])->name('users.change-password');

});


Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::get('register', [AdminAuthController::class, 'showRegistrationForm'])->name('admin.register');
Route::post('register', [AdminAuthController::class, 'register']);

// Password Reset Routes
Route::get('password/reset', [AdminAuthController::class, 'showForgotPasswordForm'])->name('admin.password.request');
Route::post('password/email', [AdminAuthController::class, 'sendResetLinkEmail'])->name('admin.password.email');
Route::get('password/reset/{token}', [AdminAuthController::class, 'showResetPasswordForm'])->name('admin.password.reset');
Route::post('password/reset', [AdminAuthController::class, 'resetPassword'])->name('admin.password.update');


Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('admin.login');
})->name('logout');
