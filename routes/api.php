<?php

use App\Http\Controllers\API\UserAuthController;
use App\Http\Controllers\API\CommentsController;
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\ArticleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
Route::post('/comments', [CommentsController::class, 'addComment']);
Route::post('/comments/reply', [CommentsController::class, 'reply']);
Route::post('/media/store', [MediaController::class, 'store']);
Route::get('/media/show', [MediaController::class, 'show']);
Route::post('/article/store', [ArticleController::class, 'store']);
Route::get('/article/show', [ArticleController::class, 'show']);
     // return $request->user();
// });
Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);

