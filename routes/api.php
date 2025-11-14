<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;


Route::middleware(['throttle:login'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    Route::middleware(['role:manager'])->group(function () {
        Route::patch('/tasks/{task}', [TaskController::class, 'update']);
        Route::post('/tasks', [TaskController::class, 'store']);
    });

    Route::middleware(['role:user'])->group(function () {
        Route::patch('/tasks/status/{task}', [TaskController::class, 'updateStatus']);
    });

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
});
