<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;


Route::middleware(['throttle:login'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::middleware(['role:manager'])->post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::middleware(['role:user'])->patch('/tasks/status/{task}', [TaskController::class, 'updateStatus']);
});
