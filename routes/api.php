<?php

use App\Http\Controllers\{AuthController, AdminController, ProductController};
use Illuminate\Support\Facades\Route;

// User Auth Routes
Route::post('/auth/otp/resend', [AuthController::class, 'resendOtp']);
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/password/forget', [AuthController::class, 'forgetPassword']);
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword'])->middleware('auth:sanctum');

// Admin Auth Routes
Route::post('/auth/admin/login', [AdminController::class, 'login']);

// Product Routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/products', [ProductController::class, 'getAllProducts']);
    Route::get('/products/{id}', [ProductController::class, 'getProductById']);
    Route::post('/products', [ProductController::class, 'createProduct'])->middleware('is_admin');
    Route::patch('/products/{id}', [ProductController::class, 'updateProduct'])->middleware('is_admin');
    Route::delete('/products/{id}', [ProductController::class, 'deleteProduct'])->middleware('is_admin');
});
