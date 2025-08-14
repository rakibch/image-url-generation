<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkRequestController;
use App\Http\Controllers\ImageTaskController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Protected APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/bulk-requests', [BulkRequestController::class, 'store']);
    Route::get('/bulk-requests/{bulkRequest}/tasks', [ImageTaskController::class, 'index']);
});
