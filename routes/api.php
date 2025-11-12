<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;


Route::middleware(['throttle:login'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


Route::middleware(['throttle:api'])->group(function () {
    Route::post('/tasks', function () {
        return "Hello world";
    });
});
