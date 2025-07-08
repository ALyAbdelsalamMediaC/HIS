<?php

use App\Http\Controllers\API\AdminCommentController;
use App\Http\Controllers\API\UserAuthController;
use App\Http\Controllers\API\CommentsController;
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\BookmarkController;
use App\Http\Controllers\API\CheckUpdateController;
use App\Http\Controllers\API\GlobalController;
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

Route::get('/categories', [MediaController::class, 'getAllCategories']);
Route::post('/sub_category/details', [MediaController::class, 'subCategoryDetails']);

Route::get('/api/media/admin-comments', [AdminCommentController::class, 'showAdminComment'])->name('admin.comments.show');
Route::post('/media/admin-comment/reply', [AdminCommentController::class, 'reply'])->name('admin.comments.reply');

Route::get('/bookmarks/{id}', [BookmarkController::class, 'getBookmarks']);
Route::post('/bookmarks/add', [BookmarkController::class, 'addBookmark']);
Route::post('/bookmarks/remove', [BookmarkController::class, 'removeBookmark']);

Route::get('/search', [GlobalController::class, 'globalSearch'])->name('global.search');

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);

Route::post('login/google', [SocialAuthController::class, 'handleGoogleLoginApi'])->name('api.social.google.login');
Route::post('/password/reset', [UserAuthController::class, 'resetPassword'])->name('api.password.reset');
Route::put('/profile', [UserAuthController::class, 'editProfile'])->name('profile.update');
Route::delete('/profile', [UserAuthController::class, 'deleteAccount'])->name('profile.delete');

Route::get('/policies', [PolicyController::class, 'index']);
Route::get('/policies/{id}', [PolicyController::class, 'show']);
Route::get('/check-update', [CheckUpdateController::class, 'get']);
Route::post('/check-update', [CheckUpdateController::class, 'update']);
