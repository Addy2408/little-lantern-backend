<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;
use App\Models\Admin;

class IsAdmin
{
    use ApiResponse;

    /**
     * Handle an incoming request.P
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if this user exists in the Admins table
        $isAdmin = Admin::where('email', $user?->email)->exists();

        if (!$isAdmin) {
            Log::warning("Unauthorized access attempt by user ID: {$user?->id}, email: {$user?->email}");
            return $this->sendError(
                403,
                'Unauthorized, Admins only.',
                ['success' => false]
            );
        }

        return $next($request);
    }
}
