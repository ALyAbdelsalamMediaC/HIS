<?php

use App\Http\Controllers\API\GuestController;
use Illuminate\Support\Facades\Route;

Route::post('/guests', [GuestController::class, 'store']);
