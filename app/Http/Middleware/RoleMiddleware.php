<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$codes)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        if (!$user->role) {
            return response()->json(['error' => 'User has no role assigned'], 403);
        }
    
        if (!in_array($user->role->code, $codes)) {
            return response()->json(['error' => 'Unauthorized - Insufficient permissions'], 403);
        }
    
        return $next($request);
    }
}
