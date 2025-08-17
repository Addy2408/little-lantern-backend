<?php

use App\Http\Controllers\{AuthController, AdminController};
use Illuminate\Support\Facades\Route;

// User Auth
Route::post('/auth/otp/resend', [AuthController::class, 'resendOtp']);
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/password/forget', [AuthController::class, 'forgetPassword']);
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword'])->middleware('auth:sanctum');

// Admin Auth
Route::post('/auth/admin/login', [AdminController::class, 'login']);
