<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (token required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Test route — permission-based access ka test
    Route::middleware('permission:users.view')->get('/test-permission', function () {
        return response()->json([
            'success' => true,
            'message' => 'You have access to view users!',
        ]);
    });
});