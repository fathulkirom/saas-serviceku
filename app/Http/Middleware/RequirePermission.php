<?php

namespace App\Http\Middleware;

use App\Models\Tenant\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    /**
     * Permission-based middleware (Blueprint v1.0 §11).
     * Usage: Route::middleware('permission:service.void')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user instanceof User) {
            abort(401);
        }

        if (!$user->canViaPermission($permission)) {
            abort(403, "Missing required permission: {$permission}");
        }

        return $next($request);
    }
}
