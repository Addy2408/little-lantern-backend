<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;

class UserController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    public function profile(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            Log::info("Profile request received", ['user_id' => $user->id]);

            Log::debug($request->all());

            $isFirstTime = empty($user->first_name) || empty($user->mobile) || empty($user->country_code);

            $rules = [
                'first_name'   => ($isFirstTime ? 'required' : 'sometimes|required') . '|string|max:255',
                'last_name'    => 'sometimes|nullable|string|max:255',
                'country_code' => ($isFirstTime ? 'required' : 'sometimes|required') . '|string|max:5',
                'mobile'       => ($isFirstTime ? 'required' : 'sometimes|required') . '|string|max:15',
                'street'       => ($isFirstTime ? 'required' : 'sometimes|required') . '|string|max:255',
                'house_number' => ($isFirstTime ? 'required' : 'sometimes|required') . '|string|max:50',
                'city'         => ($isFirstTime ? 'required' : 'sometimes|required') . '|string|max:100',
                'state'        => ($isFirstTime ? 'required' : 'sometimes|required') . '|string|max:100',
                'pin_code'     => ($isFirstTime ? 'required' : 'sometimes|required') . '|string|max:10',
                'avatar'       => 'sometimes|nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'dob'          => 'sometimes|nullable|date|before:today',
                'gender'       => 'sometimes|nullable|in:male,female,other',
            ];

            $validated = $request->validate($rules);

            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                    Log::info("Old avatar deleted", ['user_id' => $user->id]);
                }

                $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
                Log::info("New avatar uploaded", ['user_id' => $user->id, 'path' => $validated['avatar']]);
            }

            $user->fill($validated);

            if ($user->isDirty()) {
                $user->save();

                if ($isFirstTime) {
                    $message = 'User profile created successfully.';
                    $status  = 201;
                } else {
                    $message = 'User profile updated successfully.';
                    $status  = 200;
                }
            } else {
                $message = 'No changes detected.';
                $status  = 200;
            }

            Log::info($message, ['user_id' => $user->id]);

            return $this->sendResponse(
                $status,
                $message,
                [
                    'success' => true,
                    'profile' => $user->fresh(),
                ]
            );
        } catch (Exception $e) {
            Log::error("Profile save failed", [
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

    public function deleteUser(Request $request, $id)
    {
        $authUser = $request->user();

        try {
            $targetUser = User::find($id);

            if (!$targetUser) {
                Log::warning("Delete attempt failed: user not found", [
                    'requested_by_id' => $authUser->id,
                    'requested_by_role' => $authUser->role,
                    'target_user_id'  => $id,
                ]);

                return $this->sendError(404, 'User not found', ['success' => false]);
            }

            Log::info("Delete request received", [
                'requested_by_id' => $authUser->id,
                'requested_by_role' => $authUser->role,
                'target_user_id' => $id,
            ]);

            $this->authorize('delete', $targetUser);

            $targetUser->delete();

            Log::info("User deleted successfully", [
                'deleted_by_id'     => $authUser->id,
                'deleted_by_role'   => $authUser->role,
                'target_user_id'    => $id,
                'target_user_email' => $targetUser->email,
            ]);

            return $this->sendResponse(
                200,
                'User deleted successfully.',
                ['success' => true]
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized delete attempt", [
                'requested_by_id'   => $authUser->id,
                'requested_by_role' => $authUser->role,
                'target_user_id'    => $id,
            ]);

            return $this->sendError(403, 'Unauthorized action', ['success' => false]);
        } catch (Exception $e) {
            Log::error("Error while deleting user: {$e->getMessage()}", [
                'user_id'        => $id,
                'requested_by_id' => $authUser->id,
                'requested_by_role' => $authUser->role,
            ]);

            return $this->sendError(
                500,
                "Something went wrong while deleting user.",
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function fetchUsers(Request $request)
    {
        try {
            $adminId = Auth::id();
            Log::info("Fetch users request received", ["requested_by" => $adminId]);

            $perPage = (int) $request->get('per_page', 15);

            $users = User::select('id', 'first_name', 'last_name', 'email', 'created_at')
                ->where('role', 'user')
                ->orderByDesc('created_at')
                ->paginate($perPage);

            Log::info("Users fetched successfully", [
                "requested_by" => $adminId,
                "total_users" => $users->total(),
                "current_page" => $users->currentPage(),
                "per_page" => $users->perPage(),
            ]);

            return $this->sendResponse(
                200,
                "Users fetched successfully.",
                [
                    "success" => true,
                    "users" => $users
                ]
            );
        } catch (\Throwable $th) {
            Log::error("Error fetching users", [
                "error" => $th->getMessage(),
                "trace" => $th->getTraceAsString()
            ]);
            return $this->sendError(
                500,
                "Something went wrong while fetching users.",
                [
                    'success' => false,
                    'error' => $th->getMessage()
                ]
            );
        }
    }
}
