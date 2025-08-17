<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/auth/otp/resend', [AuthController::class, 'resendOtp']);
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/password/forget', [AuthController::class, 'forgetPassword']);
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword'])->middleware('auth:sanctum');
