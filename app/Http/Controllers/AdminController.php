<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Log};
use App\Models\Admin;

class AdminController extends Controller
{
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

            Log::info("Login successful for email: {$email}");

            return $this->sendResponse(
                200,
                'Login successful',
                [
                    'success' => true,
                    'token' => $token,
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
