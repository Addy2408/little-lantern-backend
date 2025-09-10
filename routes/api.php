<?php

use App\Http\Controllers\{AuthController, ProductController, UserController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All routes for authentication, products, etc. grouped logically.
| This keeps modules together and adds clarity on who can access what.
*/

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/otp/resend', [AuthController::class, 'resendOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);

    Route::post('/password/forget', [AuthController::class, 'forgetPassword']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->middleware('auth:sanctum');
});

// Product Routes
Route::prefix('products')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ProductController::class, 'getAllProducts']);
    Route::get('/{id}', [ProductController::class, 'getProductById']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/', [ProductController::class, 'createProduct']);
        Route::patch('/{id}', [ProductController::class, 'updateProduct']);
        Route::delete('/{id}', [ProductController::class, 'deleteProduct']);
    });
});

// User Routes
Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [UserController::class, 'profile']);
    Route::delete('/{id}', [UserController::class, 'deleteUser']);
    
    Route::middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'fetchUsers']);
    });
});
