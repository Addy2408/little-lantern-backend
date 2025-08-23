<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;
use Exception;

class UserController extends Controller
{
    use ApiResponse;

    public function profile(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            Log::info("Profile update request received", ['user_id' => $user->id]);

            $validated = $request->validate([
                'first_name'   => 'required|string|max:255',
                'last_name'    => 'nullable|string|max:255',
                'country_code' => 'required|string|max:5',
                'mobile'       => 'required|string|max:15',
                'street'       => 'required|string|max:255',
                'house_number' => 'required|string|max:50',
                'city'         => 'required|string|max:100',
                'state'        => 'required|string|max:100',
                'pin_code'     => 'required|string|max:10',
                'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'dob'          => 'nullable|date|before:today',
                'gender'       => 'nullable|in:male,female,other',
            ]);

            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                    Log::info("Old avatar deleted", ['user_id' => $user->id]);
                }

                $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
                Log::info("New avatar uploaded", ['user_id' => $user->id, 'path' => $validated['avatar']]);
            }

            $user->update($validated);

            Log::info("User profile updated successfully", ['user_id' => $user->id]);

            return $this->sendResponse(
                201,
                'User profile updated successfully.',
                [
                    'success' => true,
                    'profile' => $user->fresh(),
                ]
            );
        } catch (Exception $e) {
            Log::error("Profile update failed", [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return $this->sendError(
                500,
                "Something went wrong while updating user profile",
                [
                    'success' => false,
                    'error'   => $e->getMessage(),
                ]
            );
        }
    }
}
