<?php

use App\Http\Controllers\WEB\CommentController;
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

    // Route::middleware(['auth', 'session.expired'])->group(function () {
    // Route::get('/', function () {
    //     return view('pages.admin.dashboard');
    // })->name('pages.admin.dashboard');
    
    Route::resource('categories', CategoryController::class);

    Route::get('/content/videos', [MediaController::class, 'getall'])->name('content.videos');
    Route::get('/content/videos/add', [MediaController::class, 'validation'])->name('content.validation');
    Route::get('/content/videos/add', [MediaController::class, 'create']);
    Route::post('/content/videos/add', [MediaController::class, 'store'])->name('content.store');
    Route::get('/content/getone/{id}', [MediaController::class, 'getone']);
    Route::get('/content/recently_Added', [MediaController::class, 'recently_Added']);


    Route::get('/comments/add/{media_id}', [CommentController::class, 'showAddCommentForm'])->name('comments.add.form');
    Route::post('/comments/add/{media_id}', [CommentController::class, 'addComment'])->name('comments.add');
    Route::get('/comments/reply/{media_id}/{parent_id}', [CommentController::class, 'showReplyForm'])->name('comments.reply.form');
    Route::post('/comments/reply/{media_id}/{parent_id}', [CommentController::class, 'reply'])->name('comments.reply');


    Route::get('/content/articles/add', [ArticleController::class, 'create']);
    Route::post('/content/articles/add', [ArticleController::class, 'store']);
    Route::get('/content/articles', [ArticleController::class, 'getall'])->name('content.articles');
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


Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
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
    return redirect()->route('login');
})->name('logout');
