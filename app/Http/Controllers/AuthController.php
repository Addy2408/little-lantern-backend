<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Log, Mail};
use App\Models\{User};
use App\Mail\OtpMail;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;
    
    protected function sendOtp(Request $request, $message = "OTP send")
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            $email = $request->input('email');
            $otp = rand(100000, 999999);

            Log::info("Sending OTP for verification on email: {$email}");

            Mail::to($email)->send(new OtpMail($otp));

            Log::info("Email sent successfully to email: {$email}");

            User::where('email', $email)->update(['otp' => $otp]);

            return $this->sendResponse(
                200,
                "{$message}! Please check your email for verification.",
                [
                    'success' => true,
                ]
            );
        } catch (\Exception $error) {
            Log::error("Error in sending OTP: . {$error->getMessage()}");
            $this->sendError(
                500,
                "Failed to send OTP",
                [
                    'success' => false,
                    'error' => $error->getMessage(),
                ]
            );
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            $email = $request->input('email');
            $user = User::where('email', $email)->first();

            if (!$user) {
                Log::error("User does not exist with email: {$email}");
                return $this->sendError(400, 'email does not exists!', ['success' => false]);
            }

            Log::info("User exists with email: {$email}");

            return $this->sendOtp($request, "OTP resend");
        } catch (\Exception $e) {
            Log::error("Something went wrong while resending OTP: {$e->getMessage()}");

            return $this->sendError(
                500,
                'Something went wrong while resending OTP.',
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'otp' => 'required|digits:6',
                'email' => 'required|email|exists:users,email',
            ]);

            $otp = $request->input('otp');
            $email = $request->input('email');
            $user = User::where('email', $email)->first();

            if ($otp === $user->otp) {
                $user->update([
                    'otp' => null,
                    'email_verified_at' => now()
                ]);

                Log::info("OTP verified successfully for email: {$email}");

                $user->tokens()->where('expires_at', '<', now())->delete();
                $token = $user->createToken('auth_token')->plainTextToken;
                $user->tokens()->latest()->first()->update([
                    'expires_at' => now()->addDays(30)
                ]);

                return $this->sendResponse(
                    200,
                    'OTP verified successfully',
                    [
                        'success' => true,
                        'token' => $token,
                        'expires_at' => now()->addDays(30),
                    ]
                );
            }

            Log::warning('Otp verification failed for email: {$email}');

            return $this->sendError(
                400,
                'OTP verification failed for email: {$email}',
                [
                    'success' => false,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error while verifying OTP: {$e->getMessage()}");

            return $this->sendError(
                500,
                'An error occured while verifying OTP',
                [
                    'success' =>  false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function signup(Request $request)
    {
        try {
            Log::info("Signup triggered: " . json_encode($request->all()));

            $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed',
            ]);

            User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            Log::info("User created: {$request->email}");

            return $this->sendOtp($request, "OTP send");
        } catch (\Exception $e) {
            Log::error("Error during signup: {$e->getMessage()}");
            return $this->sendError(
                500,
                'An error occurred during registration',
                [
                    'success' => false,
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    public function login(Request $request)
    {
        try {
            Log::info("Login triggered: " . json_encode($request->all()));

            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:6',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::warning("Login failed: User not found for {$request->email}");
                return $this->sendError(404, 'User not found', ['success' => false]);
            }

            if (!Hash::check($request->password, $user->password)) {
                Log::warning("Login failed: Invalid password for {$request->email}");
                return $this->sendError(401, 'Invalid credentials', ['success' => false]);
            }

            if (!$user->email_verified_at) {
                Log::warning("Login failed: Email un-verified for {$user->email}");
                $this->sendOtp($request, "OTP resent for email verification");
                return $this->sendError(
                    403,
                    'Email not verified. A new OTP has been sent to your email.',
                    ['success' => false]
                );
            }

            $token = $user->createToken('authToken')->plainTextToken;

            Log::info("Login successful for email: {$user->email}");

            return $this->sendResponse(
                200,
                'Login successful',
                [
                    'success' => true,
                    'token' => $token,
                    'user' => $user,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error while login: {$e->getMessage()}");

            return $this->sendError(
                500,
                'Something went wrong while login.',
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function forgetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $email = $request->input('email');
            $user = User::where('email',  $email)->first();

            if (!$user) {
                Log::error("User does not exist with email: {$email}");

                return $this->sendError(
                    400,
                    'Email does not exists!',
                    [
                        'success' => false,
                    ]
                );
            }

            Log::info("User exists with email: {$email}");

            return $this->sendOtp($request, "OTP sent to reset password");
        } catch (\Exception $e) {
            Log::error("Something went wrong: {$e->getMessage()}");

            return $this->sendError(
                500,
                'Something went wrong!',
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|min:6|confirmed'
            ]);

            $password = $request->input('password');

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user) {
                Log::error("User does not exist!");
                return $this->sendError(400, 'Reset password failed: User does not exists!', ['success' => false,]);
            }

            $user->password = Hash::make($password);
            $user->save();

            Log::info("Password updated successfully for: {$user->email}");

            return $this->sendResponse(
                200,
                "Password reset successfully for email: {$user->email}",
                [
                    'success' => true,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Something went wrong: {$e->getMessage()}");

            return $this->sendError(
                500,
                'Something went wrong while reset password!',
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
