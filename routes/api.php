<?php

use App\Http\Controllers\API\UserAuthController;
use App\Http\Controllers\API\CommentsController;
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\ArticleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Http\Request;


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
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

// return $request->user();
// });
Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);

Route::post('login/google', [SocialAuthController::class, 'handleGoogleLoginApi'])->name('api.social.google.login');
Route::post('/password/reset', [UserAuthController::class, 'resetPassword'])->name('api.password.reset');
