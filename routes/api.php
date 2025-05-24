<?php

use App\Http\Controllers\API\UserAuthController;
use App\Http\Controllers\API\CommentsController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/comments', [CommentsController::class, 'addComment']);
Route::post('/comments/reply', [CommentsController::class, 'reply']);