<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Log};
use App\Models\Admin;
use App\Traits\ApiResponse;

class AdminController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        try {
            Log::info("Admin login attemp with email: {$request->input('email')}");

            $request->validate([
                'email' => 'required|email|exists:admins,email',
                'password' => 'required|string|min:6',
            ]);

            $email = $request->input('email');

            $admin = Admin::where('email', $email)->first();

            if (!$admin) {
                Log::warning("Login error: Admin doesnot exist with email: {$email}");
                return $this->sendError(400, 'Admin does not exist with this email.', ['success' => false,]);
            }

            if (!Hash::check($request->input('password'), $admin->password)) {
                Log::warning("Login error: Incorrect password for email: {$email}");
                return $this->sendError(401, 'Invalid credentials', ['success' => false,]);
            }

            $token = $admin->createToken('AdminToken')->plainTextToken;

            $admin->tokens()->latest()->first()->update([
                'expires_at' => now()->addDay(),
            ]);

            Log::info("Login successful for email: {$email}");

            return $this->sendResponse(
                200,
                'Login successful',
                [
                    'success' => true,
                    'token' => $token,
                    'expires_at' => now()->addDay(),
                    'admin' => $admin,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Login error: Something went wrong {$e->getMessage()}");

            return $this->sendError(
                500,
                'Something went wrong while admin login.',
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
