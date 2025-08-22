<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use Exception;

class UserController extends Controller
{
    use ApiResponse;

    public function profile(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'string|max:255',
                'country_code' => 'required|string|max:10',
                'mobile' => 'required|string|max:15',
                'steet/house' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'pin_code' => 'required|string|max:10',
            ]);

            $userId = Auth::id();

            Log::info("Fetching user profile for user ID: {$userId}");

            $profile = User::where('id', $userId)
                ->update([
                    'first_name' => $request->input('first_name'),
                    'last_name' => $request->input('last_name'),
                    'country_code' => $request->input('country_code'),
                    'mobile' => $request->input('mobile'),
                    'address' => $request->input('address'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'pin_code' => $request->input('pin_code'),
                ]);

            $this->sendResponse(
                201,
                'User profile updated successfully.',
                [
                    'success' => true,
                    'profile' => $profile,
                ]
            );
        } catch (Exception $e) {
            Log::error("Error creating user profile: {$e->getMessage()}");
            return $this->sendError(
                500,
                "Something went wrong while creating user profile",
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
