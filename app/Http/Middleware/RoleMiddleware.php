<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponse;

class RoleMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->sendError(401, 'Unauthorized. Please login first.', ['success' => false]);
        }

        if (!in_array($user->role, $roles)) {
            Log::warning("Unauthorized access attempt by user ID: {$user->id}, email: {$user->email}, role: {$user->role}");
            return $this->sendError(403, 'Forbidden. You do not have access to this resource.', ['success' => false]);
        }

        if (!$request->user()->tokenCan($user->role)) {
            return $this->sendError(403, 'Forbidden. Invalid token permissions.', ['success' => false]);
        }

        return $next($request);
    }
}
