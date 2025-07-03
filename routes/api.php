<?php

use App\Http\Controllers\API\AdminCommentController;
use App\Http\Controllers\API\UserAuthController;
use App\Http\Controllers\API\CommentsController;
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\BookmarkController;
use App\Http\Controllers\API\LikesController;
use App\Http\Controllers\API\PolicyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Http\Request;


Route::post('/comments', [CommentsController::class, 'addComment']);
Route::post('/comments/reply', [CommentsController::class, 'reply']);
Route::post('/media/store', [MediaController::class, 'store']);
Route::get('/media/show', [MediaController::class, 'show']);
Route::get('/media/recently_Added', [MediaController::class, 'recently_Added']);
Route::get('/media/featured', [MediaController::class, 'featured']);
Route::post('/article/store', [ArticleController::class, 'store']);
Route::get('/article/show', [ArticleController::class, 'show']);
Route::get('/comments/media', [CommentsController::class, 'getCommentsByMediaId']);
Route::get('/comments/article', [CommentsController::class, 'getCommentsByArticleId']);
Route::put('/media/{id}', [MediaController::class, 'update'])->name('media.update');
Route::put('/articles/{id}', [ArticleController::class, 'update'])->name('articles.update');

Route::post('/media/{mediaId}/like', [LikesController::class, 'addLike'])->name('media.like');
Route::delete('/media/{mediaId}/like', [LikesController::class, 'removeLike'])->name('media.unlike');

Route::post('/comment/{commentId}/like', [LikesController::class, 'addLikeComment'])->name('comment.like');
Route::delete('/comment/{commentId}/like', [LikesController::class, 'removeLikeComment'])->name('comment.unlike');

Route::get('/comment/{commentId}/likes', [LikesController::class, 'getLikesCommentCount'])->name('comment.likes.count');

Route::get('/categories', [MediaController::class, 'categories']);

Route::get('/api/media/admin-comments', [AdminCommentController::class, 'showAdminComment'])->name('admin.comments.show');
Route::post('/media/admin-comment/reply', [AdminCommentController::class, 'reply'])->name('admin.comments.reply');

Route::post('/bookmarks/add', [BookmarkController::class, 'addBookmark']);
Route::post('/bookmarks/remove', [BookmarkController::class, 'removeBookmark']);
Route::get('/bookmarks', [BookmarkController::class, 'getBookmarks']);


Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);

Route::post('login/google', [SocialAuthController::class, 'handleGoogleLoginApi'])->name('api.social.google.login');
Route::post('/password/reset', [UserAuthController::class, 'resetPassword'])->name('api.password.reset');
    Route::put('/profile', [UserAuthController::class, 'editProfile'])->name('profile.update');
    Route::delete('/profile', [UserAuthController::class, 'deleteAccount'])->name('profile.delete');

Route::prefix('settings/policies')->group(function () {
    Route::get('/', [PolicyController::class, 'index'])->name('policies.index');
    Route::get('/create', [PolicyController::class, 'create'])->name('policies.create');
    Route::post('/', [PolicyController::class, 'store'])->name('policies.store');
    Route::get('/{policy}', [PolicyController::class, 'show'])->name('policies.show');
    Route::get('/{policy}/edit', [PolicyController::class, 'edit'])->name('policies.edit');
    Route::put('/{policy}', [PolicyController::class, 'update'])->name('policies.update');
    Route::delete('/{policy}', [PolicyController::class, 'destroy'])->name('policies.destroy');

    Route::post('/categories', [PolicyController::class, 'storeCategory'])->name('policies.categories.store');
    Route::get('/categories/{category}', [PolicyController::class, 'showCategory'])->name('policies.categories.show');
    Route::put('/categories/{category}', [PolicyController::class, 'updateCategory'])->name('policies.categories.update');
    Route::delete('/categories/{category}', [PolicyController::class, 'destroyCategory'])->name('policies.categories.destroy');
});