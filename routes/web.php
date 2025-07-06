<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\WEB\CommentController;
use App\Http\Controllers\WEB\AdminAuthController;
use App\Http\Controllers\WEB\AdminCommentController;
use App\Http\Controllers\WEB\ArticleController;
use App\Http\Controllers\WEB\CategoryController;
use App\Http\Controllers\WEB\CommentArticleController;
use App\Http\Controllers\WEB\DashboardController;
use App\Http\Controllers\WEB\GlobalController;
use App\Http\Controllers\WEB\LikeArticleController;
use App\Http\Controllers\WEB\MediaController;
use App\Http\Controllers\WEB\PolicyController;
use App\Http\Controllers\WEB\SettingsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WEB\UserController;
use App\Http\Controllers\WEB\LikesController;
use App\Http\Controllers\WEB\ReviewsController;

Route::middleware('auth')->group(function () {

    // Route::middleware(['auth', 'session.expired'])->group(function () {
    // Route::get('/', function () {
    //     return view('pages.admin.dashboard');
    // })->name('pages.admin.dashboard');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::resource('categories', CategoryController::class);

    Route::get('/content/videos', [MediaController::class, 'getall'])->name('content.videos');
    Route::get('/content/videos/add', [MediaController::class, 'validation'])->name('content.validation');
    Route::get('/content/videos/add', [MediaController::class, 'create']);
    Route::post('/content/videos/add', [MediaController::class, 'store'])->name('content.store');
    Route::get('content/videos/{id}/edit', [MediaController::class, 'edit'])->name('content.edit');
    Route::get('/content/videos/{id}/stream', [MediaController::class, 'stream'])->name('content.stream');
    Route::get('/content/videos/{id}/{status}', [MediaController::class, 'getone'])->name('content.video');
    Route::get('/content/recently_Added', [MediaController::class, 'recently_Added']);
    Route::put('content/videos/{id}', [MediaController::class, 'update'])->name('content.update');
    Route::delete('content/videos/{id}', [MediaController::class, 'destroy'])->name('content.destroy');
    Route::delete('content/articles/{id}', [ArticleController::class, 'destroy'])->name('article.destroy');
    Route::post('/content/assigned/{id}', [MediaController::class, 'assignTo'])->name('content.assignTo');
    
    Route::get('/content/subcategories', [MediaController::class, 'getSubcategoriesByCategory'])->name('content.subcategories');

    // Route::get('/comments/add/{media_id}', [CommentController::class, 'showAddCommentForm'])->name('comments.add.form');
    Route::post('/comments/add/{media_id}', [CommentController::class, 'addComment'])->name('comments.add');
    Route::get('/comments/reply/{media_id}/{parent_id}', [CommentController::class, 'showReplyForm'])->name('comments.reply.form');
    Route::post('/comments/reply/{media_id}/{parent_id}', [CommentController::class, 'reply'])->name('comments.reply');
    Route::delete('/comments/{comment_id}', [CommentController::class, 'deleteComment'])->name('comments.delete');

    // Article comments
    Route::post('/article-comments/add/{media_id}', [CommentArticleController::class, 'addComment'])->name('article.comments.add');
    Route::get('/article-comments/reply/{media_id}/{parent_id}', [CommentArticleController::class, 'showReplyForm'])->name('article.comments.reply.form');
    Route::post('/article-comments/reply/{media_id}/{parent_id}', [CommentArticleController::class, 'reply'])->name('article.comments.reply');
    Route::delete('/article-comments/{comment_id}', [CommentArticleController::class, 'deleteComment'])->name('article.comments.delete');

    Route::post('/AdminComment/add/{media_id}', [AdminCommentController::class, 'addComment'])->name('AdminComment.add');
    Route::get('/AdminComment/reply/{media_id}/{parent_id}', [AdminCommentController::class, 'showReplyForm'])->name('AdminComment.reply.form');
    Route::post('/AdminComment/reply/{media_id}/{parent_id}', [AdminCommentController::class, 'reply'])->name('AdminComment.reply');
    Route::delete('/AdminComment/{comment_id}', [AdminCommentController::class, 'deleteComment'])->name('AdminComment.delete');

    Route::post('/reviews/add/{media_id}', [ReviewsController::class, 'addReview'])->name('reviews.add');
    Route::get('/reviews/reply/{media_id}/{parent_id}', [ReviewsController::class, 'showReplyForm'])->name('reviews.reply.form');
    Route::post('/reviews/reply/{media_id}/{parent_id}', [ReviewsController::class, 'reply'])->name('reviews.reply');
    Route::delete('/reviews/{comment_id}', [ReviewsController::class, 'deleteReview'])->name('reviews.delete');
    Route::post('/reviews/rate', [ReviewsController::class, 'rate'])->name('reviews.rate');
    Route::post('/admins/rate', [AdminCommentController::class, 'rate'])->name('admins.rate');

    Route::get('/get-google-token', [GoogleAuthController::class, 'getGoogleToken'])->name('google.auth');
    Route::get('/content/articles', [ArticleController::class, 'getall'])->name('content.articles');

    Route::get('/content/articles/add', [ArticleController::class, 'create']);
    Route::post('/content/articles/add', [ArticleController::class, 'store'])->name('articles.store');
    Route::get('content/articles/{id}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('content/articles/{id}', [ArticleController::class, 'update'])->name('articles.update');
    Route::get('/article/{id}', [ArticleController::class, 'getone'])->name('content.article');
    Route::get('/article/recently_Added', [ArticleController::class, 'recently_Added']);
    Route::get('/search', [GlobalController::class, 'globalSearch'])->name('global.search');

    Route::get('/users/', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/destroy', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/blocked', [UserController::class, 'blocked'])->name('users.blocked');

    Route::post('/media/{mediaId}', [LikesController::class, 'getLikesCommentCount'])->name('media.like.count');

    Route::post('/media/{mediaId}/like', [LikesController::class, 'addLike'])->name('media.like.add');
    Route::delete('/media/{mediaId}/like', [LikesController::class, 'removeLike'])->name('media.like.remove');

    Route::post('/media/{id}/status', [MediaController::class, 'changeStatus'])->name('media.changeStatus');

    Route::post('/comments/{commentId}/like', [LikesController::class, 'addLikeComment'])->name('comments.like.add');
    Route::delete('/comments/{commentId}/like', [LikesController::class, 'removeLikeComment'])->name('comments.like.remove');
    Route::get('/comments/{commentId}/likes/count', [LikesController::class, 'getLikesCommentCount'])->name('comments.like.count');


    Route::post('/article/{mediaId}/like', [LikeArticleController::class, 'addLike'])->name('article.like.add');
    Route::delete('/article/{mediaId}/like', [LikeArticleController::class, 'removeLike'])->name('article.like.remove');
    Route::post('/articleCmments/{commentId}/like', [LikeArticleController::class, 'addLikeComment'])->name('article.comments.like.add');
    Route::delete('/articleCmments/{commentId}/like', [LikeArticleController::class, 'removeLikeComment'])->name('article.comments.like.remove');
    Route::get('/articleCmments/{commentId}/likes/count', [LikeArticleController::class, 'getLikesCommentCount'])->name('article.comments.like.count');


    Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::get('users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::post('users/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
    Route::get('/users/add', [AdminAuthController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/users/add', [AdminAuthController::class, 'register'])->name('admin.register');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.updateProfile');
    Route::get('/settings/changePassword', [SettingsController::class, 'showChangePasswordForm'])->name('settings.showChangePasswordForm');
    Route::post('/settings/changePassword', [SettingsController::class, 'changePassword'])->name('settings.changePassword');


    Route::prefix('settings/policies')->name('policies.')->group(function () {
        Route::get('/', [PolicyController::class, 'index'])->name('index');
        Route::get('/create', [PolicyController::class, 'create'])->name('create');
        Route::post('/create', [PolicyController::class, 'store'])->name('store');
        Route::get('/{policy}', [PolicyController::class, 'show'])->name('show');
        Route::get('/{policy}/edit', [PolicyController::class, 'edit'])->name('edit');
        Route::put('/{policy}', [PolicyController::class, 'update'])->name('update');
        Route::delete('/{policy}', [PolicyController::class, 'destroy'])->name('destroy');
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::post('/', [PolicyController::class, 'storeCategory'])->name('store');
            Route::get('/{category}', [PolicyController::class, 'showCategory'])->name('show');
            Route::put('/{category}', [PolicyController::class, 'updateCategory'])->name('update');
            Route::delete('/{category}', [PolicyController::class, 'destroyCategory'])->name('destroy');
        });
    });
});


Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('admin/login', [AdminAuthController::class, 'login']);

// Password Reset Routes
Route::get('password/reset', [AdminAuthController::class, 'showForgotPasswordForm'])->name('admin.password.request');
Route::post('password/email', [AdminAuthController::class, 'sendResetLinkEmail'])->name('admin.password.email');
Route::get('password/reset/{token}', [AdminAuthController::class, 'showResetPasswordForm'])->name('admin.password.reset');
Route::post('password/reset', [AdminAuthController::class, 'resetPassword'])->name('admin.password.update');

Route::get('login/google', [SocialAuthController::class, 'redirectToGoogle'])->name('social.google.redirect');
Route::get('login/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
Route::get('login/apple', [SocialAuthController::class, 'redirectToApple'])->name('social.apple.redirect');
Route::get('login/apple/callback', [SocialAuthController::class, 'handleAppleCallback']);

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
